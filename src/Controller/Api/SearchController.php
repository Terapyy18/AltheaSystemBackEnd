<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Public search endpoint — mirrors the product:read serialization group
 * so the frontend mapProduct() function works without modification.
 */
class SearchController extends AbstractController
{
    private const PAGE_SIZE = 12;

    #[Route('/api/search', name: 'api_search', methods: ['GET'])]
    public function __invoke(Request $request, ProductRepository $repo): JsonResponse
    {
        $q     = trim((string) $request->query->get('q', ''));
        $lang  = in_array($request->query->get('lang', 'fr'), ['fr', 'en'], true)
                 ? (string) $request->query->get('lang', 'fr')
                 : 'fr';
        $limit = min(max(1, (int) $request->query->get('limit', self::PAGE_SIZE)), self::PAGE_SIZE);
        $page  = max(1, (int) $request->query->get('page', 1));

        $priceMin    = $request->query->has('price_min') ? (float) $request->query->get('price_min') : null;
        $priceMax    = $request->query->has('price_max') ? (float) $request->query->get('price_max') : null;
        $categoryIds = array_values(array_filter(
            array_map('intval', (array) $request->query->all('categories')),
            fn (int $id) => $id > 0
        ));
        $available = $request->query->getBoolean('available');

        ['total' => $total, 'products' => $products] = $repo->search(
            $q, $lang, $priceMin, $priceMax, $categoryIds, $available, $page, $limit
        );

        $totalPages = max(1, (int) ceil($total / $limit));

        $buildUrl = function (int $p) use ($q, $lang, $priceMin, $priceMax, $categoryIds, $available, $limit): string {
            $params = ['page' => $p];
            if ($q !== '') { $params['q'] = $q; }
            if ($lang !== 'fr') { $params['lang'] = $lang; }
            if ($priceMin !== null) { $params['price_min'] = $priceMin; }
            if ($priceMax !== null) { $params['price_max'] = $priceMax; }
            if (!empty($categoryIds)) { $params['categories'] = $categoryIds; }
            if ($available) { $params['available'] = '1'; }
            if ($limit !== self::PAGE_SIZE) { $params['limit'] = $limit; }
            return '/api/search?' . http_build_query($params);
        };

        $view = [
            '@id'         => $buildUrl($page),
            '@type'       => 'hydra:PartialCollectionView',
            'hydra:first' => $buildUrl(1),
            'hydra:last'  => $buildUrl($totalPages),
        ];
        if ($page > 1) { $view['hydra:previous'] = $buildUrl($page - 1); }
        if ($page < $totalPages) { $view['hydra:next'] = $buildUrl($page + 1); }

        return $this->json([
            '@context'         => '/api/contexts/Product',
            '@id'              => '/api/search',
            '@type'            => 'hydra:Collection',
            'hydra:member'     => array_map($this->serializeProduct(...), $products),
            'hydra:totalItems' => $total,
            'hydra:view'       => $view,
        ]);
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
