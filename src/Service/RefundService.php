<?php

/**
 * Refund handler for Stripe `charge.refunded` events.
 *
 * Restores stock and marks the order refunded. Like OrderService, the flow
 * is transactional and idempotent:
 *  - The order is looked up by stripe_payment_intent_id (the only id present
 *    on a Charge that we can correlate to our records).
 *  - If the order status is already "refunded", we return early — re-deliv-
 *    ered events do not re-credit stock or re-send the email.
 *  - Stock is re-incremented inside a transaction with pessimistic write
 *    locks on each product, mirroring the decrement path so the two cannot
 *    race against each other.
 */

declare(strict_types=1);

namespace App\Service;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\OrderRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\Charge as StripeCharge;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

final class RefundService
{
    private const REFUNDED_STATUS = 'refunded';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly OrderRepository $orderRepo,
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handleChargeRefunded(StripeCharge $charge): void
    {
        $intentId = is_string($charge->payment_intent ?? null) ? (string) $charge->payment_intent : null;
        $logCtx   = ['stripe_charge_id' => $charge->id ?? null, 'stripe_payment_intent_id' => $intentId];

        if ($intentId === null) {
            $this->logger->warning('[Stripe] charge.refunded without payment_intent — cannot correlate', $logCtx);
            return;
        }

        $order = $this->orderRepo->findOneBy(['stripePaymentIntentId' => $intentId]);
        if ($order === null) {
            $this->logger->warning('[Stripe] charge.refunded — no matching order', $logCtx);
            return;
        }

        $logCtx['order_id'] = $order->getId();

        if ($order->getStatus() === self::REFUNDED_STATUS) {
            $this->logger->info('[Stripe] Refund already recorded — idempotent no-op', $logCtx);
            return;
        }

        $this->em->beginTransaction();
        try {
            $stockTrace = [];

            if ($order->isStockDecremented()) {
                foreach ($order->getItemsOrders() as $line) {
                    $product = $line->getProduct();
                    if ($product === null) {
                        continue;
                    }

                    $locked = $this->em->find(Product::class, $product->getId(), LockMode::PESSIMISTIC_WRITE);
                    if ($locked === null) {
                        continue;
                    }

                    $stockBefore = (int) ($locked->getStock() ?? 0);
                    $quantity    = (int) $line->getQuantity();
                    $stockAfter  = $stockBefore + $quantity;

                    $locked->setStock($stockAfter);

                    $stockTrace[] = [
                        'product_id'   => $locked->getId(),
                        'quantity'     => $quantity,
                        'stock_before' => $stockBefore,
                        'stock_after'  => $stockAfter,
                    ];
                }

                $order->setStockDecremented(false);
            }

            $order->setStatus(self::REFUNDED_STATUS);

            $this->em->flush();
            $this->em->commit();

            $this->logger->info('[Stripe] Order refunded — stock restored', $logCtx + [
                'stock' => $stockTrace,
            ]);
        } catch (\Throwable $e) {
            if ($this->em->getConnection()->isTransactionActive()) {
                $this->em->rollback();
            }
            $this->logger->error('[Stripe] Refund transaction failed', $logCtx + [
                'error' => $e->getMessage(),
            ]);
            return;
        }

        $user = $order->getUser();
        if ($user !== null) {
            $this->sendRefundEmail($user, $order, $charge);
        }
    }

    private function sendRefundEmail(User $user, Order $order, StripeCharge $charge): void
    {
        $recipient = $user->getEmail();
        if (!$recipient) {
            return;
        }

        $amountRefunded = isset($charge->amount_refunded)
            ? ((int) $charge->amount_refunded) / 100.0
            : (float) ($order->getTotalPrice() ?? 0.0);

        $frontendUrl = (string) ($_ENV['FRONTEND_URL'] ?? 'http://localhost:3000');

        try {
            $html = $this->twig->render('emails/order_refunded.html.twig', [
                'user'             => $user,
                'order'            => $order,
                'amount_refunded'  => $amountRefunded,
                'orders_url'       => $frontendUrl . '/compte/commandes',
            ]);

            $email = (new Email())
                ->from((string) ($_ENV['MAILER_FROM'] ?? 'no-reply@althea-systems.com'))
                ->to($recipient)
                ->subject(sprintf('Remboursement de votre commande #%d', $order->getId()))
                ->html($html);

            $this->mailer->send($email);

            $this->logger->info('[Mail] Refund confirmation sent', [
                'order_id' => $order->getId(),
                'to'       => $recipient,
            ]);
        } catch (\Throwable $e) {
            $this->logger->warning('[Mail] Refund email failed', [
                'order_id' => $order->getId(),
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
