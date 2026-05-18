<?php

/**
 * Cart availability validation — public endpoint.
 *
 * Lets the frontend pre-check stock from the cart and checkout pages without
 * committing to a Stripe Checkout Session. No authentication required since a
 * cart can exist for anonymous visitors.
 *
 * The endpoint never reveals private fields: only product_id, name (SKU),
 * available_stock and requested quantity are returned for problem items.
 * Unknown product IDs are reported as `not_found` rather than silently skipped.
 */

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CartValidationController extends AbstractController
{
    private const MAX_CART_ITEMS = 50;

    #[Route('/api/cart/validate', name: 'api_cart_validate', methods: ['POST'])]
    public function validate(Request $request, ProductRepository $productRepo): JsonResponse
    {
        $data  = json_decode($request->getContent(), true);
        $items = is_array($data['items'] ?? null) ? $data['items'] : null;

        if ($items === null) {
            return $this->json(['error' => 'Invalid payload'], 400);
        }

        if (count($items) > self::MAX_CART_ITEMS) {
            return $this->json(['error' => 'Cart exceeds maximum size'], 400);
        }

        $errors = [];

        foreach ($items as $item) {
            $productId = isset($item['product_id']) ? (int) $item['product_id'] : 0;
            $quantity  = isset($item['quantity']) ? (int) $item['quantity'] : 0;

            if ($productId <= 0 || $quantity <= 0) {
                $errors[] = [
                    'product_id'      => $productId,
                    'reason'          => 'invalid_entry',
                    'requested'       => $quantity,
                    'available_stock' => 0,
                ];
                continue;
            }

            $product = $productRepo->find($productId);
            if (!$product) {
                $errors[] = [
                    'product_id'      => $productId,
                    'reason'          => 'not_found',
                    'requested'       => $quantity,
                    'available_stock' => 0,
                ];
                continue;
            }

            $availableStock = (int) ($product->getStock() ?? 0);

            if ($availableStock <= 0) {
                $errors[] = [
                    'product_id'      => $product->getId(),
                    'name'            => $product->getSku() ?: ('Produit #' . $product->getId()),
                    'reason'          => 'unavailable',
                    'requested'       => $quantity,
                    'available_stock' => 0,
                ];
                continue;
            }

            if ($availableStock < $quantity) {
                $errors[] = [
                    'product_id'      => $product->getId(),
                    'name'            => $product->getSku() ?: ('Produit #' . $product->getId()),
                    'reason'          => 'insufficient_stock',
                    'requested'       => $quantity,
                    'available_stock' => $availableStock,
                ];
            }
        }

        if ($errors !== []) {
            return $this->json(['valid' => false, 'errors' => $errors], 200);
        }

        return $this->json(['valid' => true], 200);
    }
}
