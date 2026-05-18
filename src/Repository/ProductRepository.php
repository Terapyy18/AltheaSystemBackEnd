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