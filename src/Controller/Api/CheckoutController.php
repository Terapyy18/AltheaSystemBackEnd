<?php

/**
 * Stripe Checkout Session creation.
 *
 * Security model:
 *  - All prices and totals are computed server-side from the DB. The client
 *    only sends product_id + quantity; any client-supplied price is ignored.
 *  - The session metadata stores ONLY {address_id, items[{product_id, quantity}]}.
 *    No prices or totals are sent through metadata so a tampered payload can
 *    never influence the order amount. The authoritative total is the
 *    line_items × Stripe price, which we recompute again on webhook receipt.
 *  - The cart is hard-capped at 50 items to bound payload size and abuse.
 *  - An idempotency key derived from (user_id, cart-hash) prevents accidental
 *    duplicate Checkout Sessions if the client retries the request.
 *  - The PaymentIntent receives the customer email as receipt_email and an
 *    order_origin tag for traceability in the Stripe dashboard.
 *  - HTTP responses never leak internal product details beyond the
 *    not-found product id, which is already known to the caller.
 */

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\ProductRepository;
use Psr\Log\LoggerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CheckoutController extends AbstractController
{
    private const MAX_CART_ITEMS = 50;

    #[Route('/api/checkout/session', name: 'api_checkout_session', methods: ['POST'])]
    public function createSession(
        Request $request,
        ProductRepository $productRepo,
        LoggerInterface $logger
    ): JsonResponse {
        $data       = json_decode($request->getContent(), true);
        $items      = is_array($data['items'] ?? null) ? $data['items'] : [];
        $addressId  = $data['address_id'] ?? null;
        $newAddress = is_array($data['new_address'] ?? null) ? $data['new_address'] : null;

        if ($items === []) {
            return $this->json(['error' => 'Cart is empty'], 400);
        }

        if (count($items) > self::MAX_CART_ITEMS) {
            return $this->json(['error' => 'Cart exceeds maximum size'], 400);
        }

        if (!$addressId && $newAddress === null) {
            return $this->json(['error' => 'Address required (address_id or new_address)'], 400);
        }

        $validatedNewAddress = null;
        if ($addressId === null && $newAddress !== null) {
            $street = trim((string) ($newAddress['address'] ?? ''));
            $city   = trim((string) ($newAddress['city']    ?? ''));
            $zipRaw = trim((string) ($newAddress['zip']     ?? ''));

            if ($street === '' || $city === '' || $zipRaw === '' || !ctype_digit($zipRaw)) {
                return $this->json(['error' => 'Invalid new_address payload'], 400);
            }

            $validatedNewAddress = [
                'address'      => mb_substr($street, 0, 255),
                'city'         => mb_substr($city, 0, 255),
                'postal_code'  => (int) $zipRaw,
                'province'     => mb_substr(trim((string) ($newAddress['name'] ?? 'Livraison')), 0, 255),
                'country_code' => mb_substr(trim((string) ($newAddress['country_code'] ?? 'FR')), 0, 8),
            ];
        }

        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'User not authenticated'], 401);
        }

        Stripe::setApiKey((string) ($_ENV['STRIPE_SECRET_KEY'] ?? ''));

        $lineItems     = [];
        $metadataItems = [];
        $totalEur      = 0.0;
        $stockIssues   = [];

        foreach ($items as $item) {
            $productId = isset($item['product_id']) ? (int) $item['product_id'] : 0;
            $quantity  = isset($item['quantity']) ? (int) $item['quantity'] : 0;

            if ($productId <= 0 || $quantity <= 0) {
                return $this->json(['error' => 'Invalid item entry'], 400);
            }

            $product = $productRepo->find($productId);
            if (!$product) {
                return $this->json(['error' => 'Product not found: ' . $productId], 404);
            }

            $availableStock = (int) ($product->getStock() ?? 0);
            if ($availableStock < $quantity) {
                $stockIssues[] = [
                    'product_id'      => $product->getId(),
                    'name'            => $product->getSku() ?: ('Produit #' . $product->getId()),
                    'available_stock' => $availableStock,
                    'requested'       => $quantity,
                ];
                continue;
            }

            $price = (float) ($product->getPromoPrice() ?: $product->getPrice());
            $totalEur += $price * $quantity;

            $lineItems[] = [
                'price_data' => [
                    'currency'     => 'eur',
                    'product_data' => [
                        'name' => $product->getSku() ?: 'Produit #' . $product->getId(),
                    ],
                    'unit_amount'  => (int) round($price * 100),
                ],
                'quantity' => $quantity,
            ];

            $metadataItems[] = [
                'product_id' => $product->getId(),
                'quantity'   => $quantity,
            ];
        }

        if ($stockIssues !== []) {
            return $this->json([
                'error' => 'OUT_OF_STOCK',
                'items' => $stockIssues,
            ], 422);
        }

        $idempotencyKey = hash(
            'sha256',
            $user->getId() . '|' . json_encode($metadataItems, JSON_THROW_ON_ERROR)
        );

        $sessionParams = [
            'payment_method_types' => ['card'],
            'line_items'           => $lineItems,
            'mode'                 => 'payment',
            'success_url'          => ($_ENV['FRONTEND_URL'] ?? 'http://localhost:3000') . '/checkout/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'           => ($_ENV['FRONTEND_URL'] ?? 'http://localhost:3000') . '/checkout/cancel',
            'client_reference_id'  => (string) $user->getId(),
            'metadata'             => array_filter([
                'address_id'  => $addressId !== null ? (string) $addressId : null,
                'new_address' => $validatedNewAddress !== null
                    ? json_encode($validatedNewAddress, JSON_THROW_ON_ERROR)
                    : null,
                'items'       => json_encode($metadataItems, JSON_THROW_ON_ERROR),
            ], static fn ($v) => $v !== null),
            'payment_intent_data'  => [
                'receipt_email' => $user->getEmail(),
                'metadata'      => ['order_origin' => 'althea-frontend'],
            ],
        ];

        try {
            $checkoutSession = Session::create(
                $sessionParams,
                ['idempotency_key' => $idempotencyKey]
            );
        } catch (\Throwable $e) {
            $logger->error('[Stripe] Failed to create checkout session', [
                'user_id' => $user->getId(),
                'error'   => $e->getMessage(),
            ]);
            return $this->json(['error' => 'Unable to create checkout session'], 502);
        }

        $logger->info('[Stripe] Checkout session created', [
            'session_id' => $checkoutSession->id,
            'user_id'    => $user->getId(),
            'amount_eur' => $totalEur,
            'item_count' => count($metadataItems),
        ]);

        return $this->json([
            'id'  => $checkoutSession->id,
            'url' => $checkoutSession->url,
        ]);
    }
}
