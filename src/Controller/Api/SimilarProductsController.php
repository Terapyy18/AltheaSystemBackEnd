<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class SimilarProductsController extends AbstractController
{
    #[Route(
        '/api/products/{id}/similar',
        name: 'api_products_similar',
        requirements: ['id' => '\d+'],
        methods: ['GET']
    )]
    public function __invoke(int $id, ProductRepository $repo): JsonResponse
    {
        $product = $repo->find($id);
        if (!$product) {
            throw new NotFoundHttpException('Product not found');
        }

        $similar = $repo->findSimilarProducts($product, 6);

        return $this->json(array_map($this->serializeProduct(...), $similar));
    }

    private function serializeProduct(Product $product): array
    {
        $translations = [];
        foreach ($product->getProductTranslations() as $tr) {
            $translations[] = [
                'title'       => $tr->getTitle(),
                'description' => $tr->getDescription(),
                'language'    => $tr->getLanguage(),
            ];
        }

        $categories = [];
        foreach ($product->getProductCategories() as $cat) {
            $catTranslations = [];
            foreach ($cat->getProductCategoryTranslations() as $catTr) {
                $catTranslations[] = [
                    'title'       => $catTr->getTitle(),
                    'description' => $catTr->getDescription(),
                    'language'    => $catTr->getLanguage(),
                ];
            }
            $categories[] = [
                'id'                          => $cat->getId(),
                'status'                      => $cat->isStatus(),
                'productCategoryTranslations' => $catTranslations,
            ];
        }

        $images = [];
        foreach ($product->getProductImages() as $img) {
            $images[] = ['imageUrl' => $img->getImageUrl()];
        }

        return [
            '@id'                 => '/api/products/' . $product->getId(),
            'id'                  => $product->getId(),
            'is_published'        => $product->isPublished(),
            'thumbnail'           => $product->getThumbnail(),
            'price'               => $product->getPrice(),
            'promo_price'         => $product->getPromoPrice(),
            'stock'               => $product->getStock(),
            'sku'                 => $product->getSku(),
            'priority'            => $product->getPriority(),
            'productTranslations' => $translations,
            'productCategories'   => $categories,
            'productImages'       => $images,
        ];
    }
}
