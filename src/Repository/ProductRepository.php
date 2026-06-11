<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findSimilarProducts(Product $product, int $limit = 6): array
    {
        $categoryIds = $product->getProductCategories()->map(fn($c) => $c->getId())->toArray();

        if (empty($categoryIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
        $params = [...array_values($categoryIds), $product->getId(), $limit];

        // PostgreSQL: avec SELECT DISTINCT, ORDER BY doit figurer dans le SELECT.
        // On utilise une sous-requête pour éviter cette contrainte.
        $sql = "
            SELECT id FROM (
                SELECT DISTINCT p.id, CASE WHEN p.stock > 0 THEN 0 ELSE 1 END AS avail_sort
                FROM product p
                JOIN product_product_category ppc ON ppc.product_id = p.id
                WHERE ppc.product_category_id IN ({$placeholders})
                  AND p.id != ?
                  AND p.is_published = true
            ) sub
            ORDER BY avail_sort, RANDOM()
            LIMIT ?
        ";

        $conn = $this->getEntityManager()->getConnection();
        $ids = $conn->executeQuery($sql, $params)->fetchFirstColumn();

        if (empty($ids)) {
            return [];
        }

        $products = $this->createQueryBuilder('p')
            ->leftJoin('p.productTranslations', 'pt')
            ->addSelect('pt')
            ->leftJoin('p.productCategories', 'pc')
            ->addSelect('pc')
            ->leftJoin('pc.productCategoryTranslations', 'pct')
            ->addSelect('pct')
            ->leftJoin('p.productImages', 'pi')
            ->addSelect('pi')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        $orderMap = array_flip(array_values($ids));
        usort($products, fn($a, $b) => ($orderMap[$a->getId()] ?? 999) <=> ($orderMap[$b->getId()] ?? 999));

        return $products;
    }

    /**
     * Full-text search with facets. Uses pg_trgm + f_unaccent for accent-insensitive ILIKE.
     *
     * @param array<int> $categoryIds
     * @param string     $sort  'relevance' | 'price' | 'newest' | 'availability'
     * @param string     $order 'asc' | 'desc'
     * @return array{total: int, products: Product[]}
     */
    public function search(
        string $q,
        string $lang,
        ?float $priceMin,
        ?float $priceMax,
        array $categoryIds,
        bool $availableOnly,
        int $page,
        int $limit,
        string $sort = 'relevance',
        string $order = 'asc'
    ): array {
        $conn       = $this->getEntityManager()->getConnection();
        $conditions = ['p.is_published = true'];
        $params     = ['lang' => $lang];

        if ($q !== '') {
            $conditions[] = '(f_unaccent(pt.title) ILIKE f_unaccent(:q) OR f_unaccent(pt.description) ILIKE f_unaccent(:q))';
            $params['q']  = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%';
        }

        // Effective price: promo_price when set and > 0, otherwise price
        $effectivePrice = 'CASE WHEN p.promo_price IS NOT NULL AND p.promo_price > 0 THEN p.promo_price ELSE p.price END';
        if ($priceMin !== null) {
            $conditions[]        = "{$effectivePrice} >= :price_min";
            $params['price_min'] = $priceMin;
        }
        if ($priceMax !== null) {
            $conditions[]        = "{$effectivePrice} <= :price_max";
            $params['price_max'] = $priceMax;
        }

        if ($availableOnly) {
            $conditions[] = 'p.stock > 0';
        }

        $fromJoin = 'FROM product p INNER JOIN product_translation pt ON pt.product_id = p.id AND pt.language = :lang';

        if (!empty($categoryIds)) {
            // Safe: values cast to int before inlining
            $safeIds  = implode(',', array_map('intval', $categoryIds));
            $fromJoin .= " INNER JOIN (SELECT DISTINCT product_id FROM product_product_category WHERE product_category_id IN ({$safeIds})) ppc ON ppc.product_id = p.id";
        }

        $where = 'WHERE ' . implode(' AND ', $conditions);

        $total = (int) $conn->executeQuery("SELECT COUNT(DISTINCT p.id) {$fromJoin} {$where}", $params)->fetchOne();

        // Build IDs query with sort.
        // For non-trivial sorts a subquery is required: SELECT DISTINCT in PostgreSQL only allows
        // ORDER BY on expressions that appear in the SELECT list.
        $offset   = ($page - 1) * $limit;
        $orderDir = $order === 'desc' ? 'DESC' : 'ASC';

        if ($sort === 'relevance') {
            $ids = $conn->executeQuery(
                "SELECT DISTINCT p.id {$fromJoin} {$where} ORDER BY p.id ASC LIMIT {$limit} OFFSET {$offset}",
                $params
            )->fetchFirstColumn();
        } else {
            $sortExpr = match ($sort) {
                'price'        => $effectivePrice,
                'newest'       => 'p.create_at',
                'availability' => 'CASE WHEN p.stock > 0 THEN 0 ELSE 1 END',
                default        => 'p.id',
            };
            $ids = $conn->executeQuery(
                "SELECT id FROM (SELECT DISTINCT p.id, {$sortExpr} AS sort_col {$fromJoin} {$where}) sub ORDER BY sort_col {$orderDir}, id ASC LIMIT {$limit} OFFSET {$offset}",
                $params
            )->fetchFirstColumn();
        }

        if (empty($ids)) {
            return ['total' => $total, 'products' => []];
        }

        $products = $this->createQueryBuilder('p')
            ->leftJoin('p.productTranslations', 'pt')->addSelect('pt')
            ->leftJoin('p.productCategories', 'pc')->addSelect('pc')
            ->leftJoin('pc.productCategoryTranslations', 'pct')->addSelect('pct')
            ->leftJoin('p.productImages', 'pi')->addSelect('pi')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        $orderMap = array_flip(array_values($ids));
        usort($products, fn ($a, $b) => ($orderMap[$a->getId()] ?? 999) <=> ($orderMap[$b->getId()] ?? 999));

        return ['total' => $total, 'products' => $products];
    }

    public function criticalStock(): array
    {
        $raw = $this->createQueryBuilder('p')
            ->select('p.id, p.sku, p.stock, pt.title AS name')
            ->leftJoin('p.productTranslations', 'pt', 'WITH', 'pt.language = :lang')
            ->setParameter('lang', 'fr')
            ->where('p.stock <= :threshold')
            ->setParameter('threshold', 5) // adapte ce seuil selon ton besoin
            ->orderBy('p.stock', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(fn($r) => [
            'name'           => $r['name'] ?? $r['sku'],
            'reference'      => $r['sku'],
            'stock'          => $r['stock'],
            'stockThreshold' => 5, // même valeur que ci-dessus
        ], $raw);
    }

//    /**
//     * @return Product[] Returns an array of Product objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Product
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}