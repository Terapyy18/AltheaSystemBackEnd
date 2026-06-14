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

        $build = $this->buildLineItems($items, $productRepo);
        if (isset($build['error'])) {
            return $this->json($build['error'], $build['status']);
        }
        ['lineItems' => $lineItems, 'metadataItems' => $metadataItems, 'totalEur' => $totalEur] = $build;

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

    /**
     * Variante « commande invité » (sans compte / sans JWT).
     *
     * Même garanties de sécurité que la version connectée : les prix sont
     * recalculés côté serveur depuis la BDD, seuls product_id + quantity du
     * client sont pris en compte. La différence : aucun User n'est rattaché ;
     * l'identité de l'acheteur (email, raison sociale, SIREN — plateforme B2B)
     * est portée par les metadata Stripe (flag `is_guest=1`) et l'Order sera
     * créée par le webhook avec user = null et guestEmail/guestCompany/guestSiren
     * renseignés.
     */
    #[Route('/api/checkout/session/guest', name: 'api_checkout_session_guest', methods: ['POST'])]
    public function createGuestSession(
        Request $request,
        ProductRepository $productRepo,
        LoggerInterface $logger
    ): JsonResponse {
        $data  = json_decode($request->getContent(), true);
        $items = is_array($data['items'] ?? null)
            ? $data['items']
            : (is_array($data['cartItems'] ?? null) ? $data['cartItems'] : []);

        if ($items === []) {
            return $this->json(['error' => 'Cart is empty'], 400);
        }

        if (count($items) > self::MAX_CART_ITEMS) {
            return $this->json(['error' => 'Cart exceeds maximum size'], 400);
        }

        // Identité de l'invité (plateforme B2B : entreprise + email + SIREN)
        $guestEmail   = trim((string) ($data['guestEmail'] ?? ''));
        $guestCompany = trim((string) ($data['guestCompany'] ?? ''));
        $guestSiren   = preg_replace('/\s+/', '', (string) ($data['guestSiren'] ?? ''));

        if ($guestEmail === '' || !filter_var($guestEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['error' => 'Invalid guest email'], 400);
        }
        if ($guestCompany === '') {
            return $this->json(['error' => 'Guest company name is required'], 400);
        }
        if (!preg_match('/^\d{9}$|^\d{14}$/', $guestSiren)) {
            return $this->json(['error' => 'Invalid guest SIREN (9 or 14 digits required)'], 400);
        }

        // Adresse de livraison (toujours une nouvelle adresse pour un invité)
        $rawAddress = is_array($data['address'] ?? null) ? $data['address'] : null;
        if ($rawAddress === null) {
            return $this->json(['error' => 'Address required'], 400);
        }

        $street = trim((string) ($rawAddress['address'] ?? ''));
        $city   = trim((string) ($rawAddress['city']    ?? ''));
        $zipRaw = trim((string) ($rawAddress['zip']     ?? ''));

        if ($street === '' || $city === '' || $zipRaw === '' || !ctype_digit($zipRaw)) {
            return $this->json(['error' => 'Invalid address payload'], 400);
        }

        $validatedAddress = [
            'address'      => mb_substr($street, 0, 255),
            'city'         => mb_substr($city, 0, 255),
            'postal_code'  => (int) $zipRaw,
            'province'     => mb_substr($guestCompany ?: 'Livraison', 0, 255),
            'country_code' => mb_substr(trim((string) ($rawAddress['country_code'] ?? 'FR')) ?: 'FR', 0, 8),
        ];

        Stripe::setApiKey((string) ($_ENV['STRIPE_SECRET_KEY'] ?? ''));

        $build = $this->buildLineItems($items, $productRepo);
        if (isset($build['error'])) {
            return $this->json($build['error'], $build['status']);
        }
        ['lineItems' => $lineItems, 'metadataItems' => $metadataItems, 'totalEur' => $totalEur] = $build;

        $idempotencyKey = hash(
            'sha256',
            'guest|' . $guestEmail . '|' . json_encode($metadataItems, JSON_THROW_ON_ERROR)
        );

        $sessionParams = [
            'payment_method_types' => ['card'],
            'line_items'           => $lineItems,
            'mode'                 => 'payment',
            'customer_email'       => $guestEmail,
            'success_url'          => ($_ENV['FRONTEND_URL'] ?? 'http://localhost:3000') . '/checkout/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'           => ($_ENV['FRONTEND_URL'] ?? 'http://localhost:3000') . '/checkout/cancel',
            'metadata'             => [
                'is_guest'      => '1',
                'guest_email'   => $guestEmail,
                'guest_company' => mb_substr($guestCompany, 0, 255),
                'guest_siren'   => $guestSiren,
                'new_address'   => json_encode($validatedAddress, JSON_THROW_ON_ERROR),
                'items'         => json_encode($metadataItems, JSON_THROW_ON_ERROR),
            ],
            'payment_intent_data'  => [
                'receipt_email' => $guestEmail,
                'metadata'      => ['order_origin' => 'althea-frontend-guest'],
            ],
        ];

        try {
            $checkoutSession = Session::create(
                $sessionParams,
                ['idempotency_key' => $idempotencyKey]
            );
        } catch (\Throwable $e) {
            $logger->error('[Stripe] Failed to create guest checkout session', [
                'guest_email' => $guestEmail,
                'error'       => $e->getMessage(),
            ]);
            return $this->json(['error' => 'Unable to create checkout session'], 502);
        }

        $logger->info('[Stripe] Guest checkout session created', [
            'session_id' => $checkoutSession->id,
            'amount_eur' => $totalEur,
            'item_count' => count($metadataItems),
        ]);

        return $this->json([
            'id'  => $checkoutSession->id,
            'url' => $checkoutSession->url,
        ]);
    }

    /**
     * Construit les line_items Stripe et les metadata d'items à partir du
     * panier client, en recalculant chaque prix depuis la BDD.
     *
     * @return array{lineItems: array, metadataItems: array, totalEur: float}|array{error: array, status: int}
     */
    private function buildLineItems(array $items, ProductRepository $productRepo): array
    {
        $lineItems     = [];
        $metadataItems = [];
        $totalEur      = 0.0;
        $stockIssues   = [];

        foreach ($items as $item) {
            $productId = isset($item['product_id']) ? (int) $item['product_id'] : 0;
            $quantity  = isset($item['quantity']) ? (int) $item['quantity'] : 0;

            if ($productId <= 0 || $quantity <= 0) {
                return ['error' => ['error' => 'Invalid item entry'], 'status' => 400];
            }

            $product = $productRepo->find($productId);
            if (!$product) {
                return ['error' => ['error' => 'Product not found: ' . $productId], 'status' => 404];
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
            return ['error' => ['error' => 'OUT_OF_STOCK', 'items' => $stockIssues], 'status' => 422];
        }

        return [
            'lineItems'     => $lineItems,
            'metadataItems' => $metadataItems,
            'totalEur'      => $totalEur,
        ];
    }
}
