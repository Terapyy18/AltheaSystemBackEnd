<?php

/**
 * Order creation from a Stripe Checkout Session.
 *
 * Idempotency contract:
 *  - A unique index on order.stripe_session_id guarantees we cannot persist
 *    two orders for the same session, even under concurrent webhook delivery.
 *  - Before touching anything we look the session up; if an order already
 *    exists we return it unchanged (no stock movement, no second email).
 *  - Inside the transaction we re-check the existence right before insert
 *    to close the TOCTOU window between the initial read and the write.
 *  - Stock decrementation is protected by `stock_decremented` on the Order
 *    plus pessimistic row locks on Product, so a re-played webhook that
 *    somehow bypasses the first guard still cannot double-decrement.
 *
 * Transactional flow:
 *  1. Open a Doctrine transaction.
 *  2. SELECT ... FOR UPDATE on each Product (LockMode::PESSIMISTIC_WRITE).
 *  3. If any product lacks stock → rollback, alert log, notify admin, return null.
 *  4. Decrement stock, persist Order + ItemsOrder, set stockDecremented = true.
 *  5. Compare recomputed total to Stripe's authoritative amount_total. On
 *     mismatch the order is still persisted but marked "Suspicious".
 *  6. Flush + commit.
 *  7. AFTER commit: send the customer confirmation email. A failure here
 *     never undoes the order — emails are best-effort and just logged.
 */

declare(strict_types=1);

namespace App\Service;

use App\Entity\Addresses;
use App\Entity\ItemsOrder;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Service\InvoiceService;
use App\Repository\AddressesRepository;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\Checkout\Session as StripeSession;
use Stripe\PaymentIntent as StripePaymentIntent;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

final class OrderService
{
    private const PRICE_TOLERANCE_EUR = 0.01;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly OrderRepository $orderRepo,
        private readonly AddressesRepository $addressesRepo,
        private readonly UserRepository $userRepo,
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
        private readonly LoggerInterface $logger,
        private readonly InvoiceService $invoiceService,
    ) {
    }

    public function createOrderFromStripeSession(StripeSession $session, User $user): ?Order
    {
        $sessionId = (string) $session->id;
        $logCtx    = ['stripe_session_id' => $sessionId, 'user_id' => $user->getId()];

        $existing = $this->orderRepo->findOneBy(['stripeSessionId' => $sessionId]);
        if ($existing !== null) {
            $this->logger->info('[Order] Already processed — idempotent no-op', $logCtx + [
                'order_id' => $existing->getId(),
            ]);
            return $existing;
        }

        $metadata        = $session->metadata ?? null;
        $addressId       = $metadata->address_id ?? null;
        $rawNewAddress   = $metadata->new_address ?? null;
        $rawItems        = $metadata->items ?? '[]';

        if ($session->amount_total === null) {
            $this->logger->error('[Order] Missing amount_total on session', $logCtx);
            return null;
        }
        $stripeTotalEur = ((int) $session->amount_total) / 100.0;

        $address = null;
        $addressIsNew = false;
        if ($addressId !== null) {
            $address = $this->addressesRepo->find((int) $addressId);
        } elseif (is_string($rawNewAddress) && $rawNewAddress !== '') {
            $decoded = json_decode($rawNewAddress, true);
            if (is_array($decoded)) {
                $address = $this->buildAddressFromMetadata($decoded, $user);
                $addressIsNew = $address !== null;
            }
        }

        if ($address === null) {
            $this->logger->error('[Order] Address not resolved (missing address_id and invalid new_address)', $logCtx + [
                'address_id'        => $addressId,
                'has_new_address'   => $rawNewAddress !== null,
            ]);
            return null;
        }

        $items = json_decode((string) $rawItems, true);
        if (!is_array($items) || $items === []) {
            $this->logger->error('[Order] Items metadata missing or invalid', $logCtx);
            return null;
        }

        $order = null;
        $stockTrace = [];

        $this->em->beginTransaction();
        try {
            $existing = $this->orderRepo->findOneBy(['stripeSessionId' => $sessionId]);
            if ($existing !== null) {
                $this->em->rollback();
                $this->logger->info('[Order] Race with concurrent webhook — returning existing', $logCtx + [
                    'order_id' => $existing->getId(),
                ]);
                return $existing;
            }

            if ($addressIsNew) {
                $this->em->persist($address);
            }

            $order = new Order();
            $order->setUser($user);
            $order->setAddresses($address);
            $order->setPayedAt(new \DateTime());
            $order->setStripeSessionId($sessionId);
            $paymentIntentId = $session->payment_intent ?? null;
            if (is_string($paymentIntentId) && $paymentIntentId !== '') {
                $order->setStripePaymentIntentId($paymentIntentId);
            }
            $order->setStockDecremented(false);
            $this->em->persist($order);

            $recomputedTotal = 0.0;
            $itemCount       = 0;

            foreach ($items as $itemData) {
                $productId = isset($itemData['product_id']) ? (int) $itemData['product_id'] : 0;
                $quantity  = isset($itemData['quantity']) ? (int) $itemData['quantity'] : 0;

                if ($productId <= 0 || $quantity <= 0) {
                    $this->logger->warning('[Order] Invalid item entry — skipped', $logCtx + ['item' => $itemData]);
                    continue;
                }

                $product = $this->em->find(Product::class, $productId, LockMode::PESSIMISTIC_WRITE);
                if ($product === null) {
                    $this->logger->warning('[Order] Product not found — skipped', $logCtx + ['product_id' => $productId]);
                    continue;
                }

                $stockBefore = (int) ($product->getStock() ?? 0);
                if ($stockBefore < $quantity) {
                    $this->em->rollback();
                    $this->logger->alert('[Order] Insufficient stock at webhook time', $logCtx + [
                        'product_id'      => $productId,
                        'requested'       => $quantity,
                        'available_stock' => $stockBefore,
                    ]);
                    $this->notifyAdminStockIssue($sessionId, $user, $productId, $quantity, $stockBefore);
                    return null;
                }

                $stockAfter = $stockBefore - $quantity;
                $product->setStock($stockAfter);

                $unitPrice        = (float) ($product->getPromoPrice() ?: $product->getPrice());
                $recomputedTotal += $unitPrice * $quantity;
                $itemCount++;

                $itemsOrder = new ItemsOrder();
                $itemsOrder->setOrder($order);
                $itemsOrder->setProduct($product);
                $itemsOrder->setQuantity($quantity);
                $itemsOrder->setPrice($unitPrice);
                $this->em->persist($itemsOrder);

                $stockTrace[] = [
                    'product_id'   => $productId,
                    'quantity'     => $quantity,
                    'stock_before' => $stockBefore,
                    'stock_after'  => $stockAfter,
                ];
            }

            $delta      = abs($recomputedTotal - $stripeTotalEur);
            $suspicious = $delta > self::PRICE_TOLERANCE_EUR;

            $order->setStatus($suspicious ? 'Suspicious' : 'Payée');
            $order->setTotalPrice($stripeTotalEur);
            $order->setStockDecremented(true);

            if ($suspicious) {
                $this->logger->alert('[Order] Amount mismatch — manual review required', $logCtx + [
                    'expected_eur'   => $stripeTotalEur,
                    'recomputed_eur' => $recomputedTotal,
                    'delta_eur'      => $delta,
                ]);
            }

            $this->em->flush();
            $this->em->commit();

            $this->logger->info('[Order] Persisted successfully', $logCtx + [
                'order_id'   => $order->getId(),
                'status'     => $order->getStatus(),
                'total_eur'  => $stripeTotalEur,
                'item_count' => $itemCount,
                'stock'      => $stockTrace,
            ]);
        } catch (\Throwable $e) {
            if ($this->em->getConnection()->isTransactionActive()) {
                $this->em->rollback();
            }
            $this->logger->error('[Order] Transaction failed — rolled back', $logCtx + [
                'error' => $e->getMessage(),
            ]);
            return null;
        }

        // Génération de la facture PDF (post-commit, non bloquant)
        try {
            $this->invoiceService->generateForOrder($order);
        } catch (\Throwable $e) {
            $this->logger->warning('[Order] Invoice generation failed (order still committed)', $logCtx + [
                'order_id' => $order->getId(),
                'error'    => $e->getMessage(),
            ]);
        }

        try {
            $this->sendConfirmationEmail($user, $order);
        } catch (\Throwable $e) {
            $this->logger->warning('[Order] Confirmation email failed (order still committed)', $logCtx + [
                'order_id' => $order->getId(),
                'error'    => $e->getMessage(),
            ]);
        }

        return $order;
    }

    private function buildAddressFromMetadata(array $data, User $user): ?Addresses
    {
        $street      = trim((string) ($data['address']      ?? ''));
        $city        = trim((string) ($data['city']         ?? ''));
        $postalCode  = $data['postal_code'] ?? null;
        $province    = trim((string) ($data['province']     ?? 'Livraison'));
        $countryCode = trim((string) ($data['country_code'] ?? 'FR'));

        if ($street === '' || $city === '' || !is_int($postalCode)) {
            return null;
        }

        $address = new Addresses();
        $address->setAddress($street);
        $address->setCity($city);
        $address->setPostalCode($postalCode);
        $address->setProvince($province);
        $address->setCountryCode($countryCode);
        $address->setUser($user);

        return $address;
    }

    public function handleSessionCompleted(StripeSession $session): void
    {
        $userId = $session->client_reference_id ?? null;
        $user   = $userId !== null ? $this->userRepo->find((int) $userId) : null;

        if ($user === null) {
            $this->logger->error('[Stripe] checkout.session.completed — unknown user', [
                'stripe_session_id' => $session->id ?? null,
                'user_id'           => $userId,
            ]);
            return;
        }

        $this->createOrderFromStripeSession($session, $user);
    }

    public function handleSessionExpired(StripeSession $session): void
    {
        $sessionId = (string) $session->id;
        $userId    = $session->client_reference_id ?? null;
        $logCtx    = ['stripe_session_id' => $sessionId, 'user_id' => $userId];

        $existing = $this->orderRepo->findOneBy(['stripeSessionId' => $sessionId]);
        if ($existing !== null) {
            $this->logger->info('[Stripe] session.expired arrived after order was completed — ignored', $logCtx + [
                'order_id' => $existing->getId(),
            ]);
            return;
        }

        $user = $userId !== null ? $this->userRepo->find((int) $userId) : null;
        if ($user === null) {
            $this->logger->info('[Stripe] session.expired — unknown user, skipping email', $logCtx);
            return;
        }

        $amountTotal = $session->amount_total ?? null;
        $amountEur   = $amountTotal !== null ? ((int) $amountTotal) / 100.0 : null;

        $this->logger->info('[Stripe] session.expired', $logCtx + [
            'amount_eur' => $amountEur,
        ]);

        $this->sendTemplatedEmail(
            $user,
            'emails/session_expired.html.twig',
            'Votre commande Althea Systems n\'a pas été finalisée',
            [
                'user'       => $user,
                'amount_eur' => $amountEur,
                'resume_url' => $this->frontendUrl() . '/panier',
            ],
            $logCtx
        );
    }

    public function handlePaymentFailed(StripePaymentIntent $paymentIntent): void
    {
        $intentId = (string) $paymentIntent->id;
        $logCtx   = ['stripe_payment_intent_id' => $intentId];

        $lastError = $paymentIntent->last_payment_error ?? null;
        $reason    = is_object($lastError) ? ($lastError->message ?? null) : null;
        $code      = is_object($lastError) ? ($lastError->code ?? null) : null;

        $order = $this->orderRepo->findOneBy(['stripePaymentIntentId' => $intentId]);

        if ($order === null) {
            $this->logger->warning('[Stripe] payment_intent.payment_failed — no matching order', $logCtx + [
                'code'   => $code,
                'reason' => $reason,
            ]);
            return;
        }

        if ($order->getStatus() === 'Echec paiement') {
            $this->logger->info('[Stripe] payment_failed already recorded — idempotent no-op', $logCtx + [
                'order_id' => $order->getId(),
            ]);
            return;
        }

        $order->setStatus('Echec paiement');
        $this->em->flush();

        $this->logger->warning('[Stripe] Order marked as payment failed', $logCtx + [
            'order_id' => $order->getId(),
            'code'     => $code,
            'reason'   => $reason,
        ]);

        $user = $order->getUser();
        if ($user === null) {
            return;
        }

        $this->sendTemplatedEmail(
            $user,
            'emails/payment_failed.html.twig',
            sprintf('Échec du paiement - Commande #%d', $order->getId()),
            [
                'user'        => $user,
                'order'       => $order,
                'reason'      => $this->humanizeStripeError($code, $reason),
                'retry_url'   => $this->frontendUrl() . '/panier',
            ],
            $logCtx + ['order_id' => $order->getId()]
        );
    }

    private function humanizeStripeError(?string $code, ?string $fallback): string
    {
        return match ($code) {
            'card_declined'        => "Votre carte a été refusée par votre banque.",
            'expired_card'         => "Votre carte est expirée.",
            'incorrect_cvc'        => "Le code de sécurité (CVC) de votre carte est incorrect.",
            'insufficient_funds'   => "Le solde de votre compte est insuffisant.",
            'processing_error'     => "Une erreur est survenue côté banque pendant le traitement.",
            'authentication_required' => "Votre banque a demandé une authentification supplémentaire qui n'a pas abouti.",
            default                => $fallback ?? "Votre paiement n'a pas pu être validé.",
        };
    }

    private function frontendUrl(): string
    {
        return (string) ($_ENV['FRONTEND_URL'] ?? 'http://localhost:3000');
    }

    private function sendTemplatedEmail(User $user, string $template, string $subject, array $vars, array $logCtx): void
    {
        $recipient = $user->getEmail();
        if (!$recipient) {
            $this->logger->warning('[Mail] Skipped — user has no email', $logCtx);
            return;
        }

        try {
            $html  = $this->twig->render($template, $vars);
            $email = (new Email())
                ->from((string) ($_ENV['MAILER_FROM'] ?? 'no-reply@althea-systems.com'))
                ->to($recipient)
                ->subject($subject)
                ->html($html);

            $this->mailer->send($email);

            $this->logger->info('[Mail] Sent', $logCtx + ['template' => $template, 'to' => $recipient]);
        } catch (\Throwable $e) {
            $this->logger->warning('[Mail] Send failed', $logCtx + [
                'template' => $template,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    private function sendConfirmationEmail(User $user, Order $order): void
    {
        $recipient = $user->getEmail();
        if (!$recipient) {
            $this->logger->warning('[Order] Skipping confirmation email — user has no address', [
                'order_id' => $order->getId(),
                'user_id'  => $user->getId(),
            ]);
            return;
        }

        $items = [];
        $totalAmount = 0.0;
        foreach ($order->getItemsOrders() as $line) {
            $product     = $line->getProduct();
            $title       = null;
            if ($product !== null) {
                $tr    = $product->getProductTranslations()->first() ?: null;
                $title = $tr ? $tr->getTitle() : ($product->getSku() ?: ('Produit #' . $product->getId()));
            }

            $lineTotal    = (float) $line->getPrice() * (int) $line->getQuantity();
            $totalAmount += $lineTotal;

            $items[] = [
                'title'      => $title ?? 'Produit',
                'quantity'   => (int) $line->getQuantity(),
                'unit_price' => (float) $line->getPrice(),
                'line_total' => $lineTotal,
            ];
        }

        $frontendUrl = (string) ($_ENV['FRONTEND_URL'] ?? 'http://localhost:3000');
        $fromAddress = (string) ($_ENV['MAILER_FROM'] ?? 'no-reply@althea-systems.com');

        $html = $this->twig->render('emails/order_confirmation.html.twig', [
            'user'         => $user,
            'order'        => $order,
            'items'        => $items,
            'totalAmount'  => $order->getTotalPrice() ?? $totalAmount,
            'address'      => $order->getAddresses(),
            'orders_url'   => $frontendUrl . '/compte/commandes',
            'invoice_note' => 'available_in_account',
        ]);

        $email = (new Email())
            ->from($fromAddress)
            ->to($recipient)
            ->subject(sprintf('Commande #%d confirmée - Althea Systems', $order->getId()))
            ->html($html);

        $this->mailer->send($email);

        $this->logger->info('[Order] Confirmation email sent', [
            'order_id' => $order->getId(),
            'to'       => $recipient,
        ]);
    }

    private function notifyAdminStockIssue(
        string $sessionId,
        User $user,
        int $productId,
        int $requested,
        int $available
    ): void {
        $adminEmail = (string) ($_ENV['ADMIN_EMAIL'] ?? '');
        if ($adminEmail === '') {
            $this->logger->alert('[Order] Admin alert SKIPPED — ADMIN_EMAIL not set', [
                'stripe_session_id' => $sessionId,
                'user_id'           => $user->getId(),
                'product_id'        => $productId,
                'requested'         => $requested,
                'available'         => $available,
            ]);
            return;
        }

        try {
            $email = (new Email())
                ->from((string) ($_ENV['MAILER_FROM'] ?? 'no-reply@althea-systems.com'))
                ->to($adminEmail)
                ->subject('[ALERTE] Stock insuffisant après paiement Stripe')
                ->text(sprintf(
                    "Stock insuffisant détecté lors du traitement d'un webhook payé.\n\n" .
                    "Session Stripe: %s\nUtilisateur: %d (%s)\nProduit: %d\nDemandé: %d\nDisponible: %d\n\n" .
                    "Le paiement a été encaissé mais l'Order n'a PAS été créée. Action manuelle requise.",
                    $sessionId,
                    (int) $user->getId(),
                    (string) $user->getEmail(),
                    $productId,
                    $requested,
                    $available
                ));

            $this->mailer->send($email);
        } catch (\Throwable $e) {
            $this->logger->error('[Order] Failed to send admin stock alert', [
                'stripe_session_id' => $sessionId,
                'error'             => $e->getMessage(),
            ]);
        }
    }
}
