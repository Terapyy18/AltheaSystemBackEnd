<?php

/**
 * Order lookup by Stripe Checkout Session id.
 *
 * Used by the post-payment success page to reload the order even after the
 * user refreshes or arrives later through the receipt email. The endpoint
 * enforces ownership: the authenticated user must be the order's owner.
 *
 * Returns 404 when no order exists yet (the Stripe webhook may not have run
 * yet). The frontend polls this endpoint for a short window.
 */

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class OrderLookupController extends AbstractController
{
    #[Route(
        '/api/orders/by-stripe-session/{sessionId}',
        name: 'api_orders_by_stripe_session',
        requirements: ['sessionId' => '[A-Za-z0-9_]+'],
        methods: ['GET']
    )]
    public function getByStripeSession(string $sessionId, OrderRepository $orderRepo): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthenticated'], 401);
        }

        $order = $orderRepo->findOneBy(['stripeSessionId' => $sessionId]);
        if (!$order) {
            return $this->json(['error' => 'Order not found'], 404);
        }

        if ($order->getUser() !== $user) {
            return $this->json(['error' => 'Order not found'], 404);
        }

        return $this->json($this->serializeOrder($order));
    }

    private function serializeOrder(Order $order): array
    {
        $address = $order->getAddresses();

        $items = [];
        foreach ($order->getItemsOrders() as $itemOrder) {
            $product = $itemOrder->getProduct();
            $translations = [];

            if ($product !== null) {
                foreach ($product->getProductTranslations() as $tr) {
                    $translations[] = [
                        'title'       => $tr->getTitle(),
                        'description' => $tr->getDescription(),
                        'lang'        => $tr->getLanguage(),
                    ];
                }
            }

            $items[] = [
                'quantity' => $itemOrder->getQuantity(),
                'price'    => $itemOrder->getPrice(),
                'product'  => $product === null ? null : [
                    'id_product'  => $product->getId(),
                    'sku'         => $product->getSku(),
                    'thumbnail'   => $product->getThumbnail(),
                    'price'       => $product->getPrice(),
                    'promo_price' => $product->getPromoPrice(),
                    'translations' => $translations,
                ],
            ];
        }

        return [
            'id'                => $order->getId(),
            'stripe_session_id' => $order->getStripeSessionId(),
            'status'            => $order->getStatus(),
            'total'             => $order->getTotalPrice(),
            'created_at'        => $order->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'payed_at'          => $order->getPayedAt()?->format(\DateTimeInterface::ATOM),
            'items'             => $items,
            'address'           => $address === null ? null : [
                'id'      => $address->getId(),
                'address' => $address->getAddress(),
                'city'    => $address->getCity(),
                'zip'     => (string) $address->getPostalCode(),
            ],
        ];
    }
}
