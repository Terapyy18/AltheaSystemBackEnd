<?php

namespace App\Repository;

use App\Entity\ItemsOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ItemsOrder>
 */
class ItemsOrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ItemsOrder::class);
    }

    public function totalSoldThisMonth(int $month, int $year): int
    {
        return (int) $this->createQueryBuilder('oi')
            ->select('SUM(oi.quantity)')
            ->join('oi.order', 'o')
            ->where('MONTH(o.createdAt) = :month AND YEAR(o.createdAt) = :year')
            ->setParameters(['month' => $month, 'year' => $year])
            ->getQuery()->getSingleScalarResult();
    }

    public function revenueThisMonth(int $month, int $year): float
    {
        return (float) $this->createQueryBuilder('oi')
            ->select('SUM(oi.quantity * oi.unitPrice)')
            ->join('oi.order', 'o')
            ->where('MONTH(o.createdAt) = :month AND YEAR(o.createdAt) = :year')
            ->setParameters(['month' => $month, 'year' => $year])
            ->getQuery()->getSingleScalarResult();
    }

    public function salesLast12Months(): array
    {
        $raw = $this->createQueryBuilder('oi')
            ->select('YEAR(o.createdAt) AS year, MONTH(o.createdAt) AS month, SUM(oi.quantity) AS total')
            ->join('oi.order', 'o')
            ->where('o.createdAt >= :start')
            ->setParameter('start', new \DateTime('-12 months'))
            ->groupBy('year, month')
            ->orderBy('year, month')
            ->getQuery()->getResult();

        // Formater pour le front
        $months = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
        return array_map(fn($r) => [
            'label' => $months[(int)$r['month'] - 1] . ' ' . $r['year'],
            'total' => (int) $r['total'],
        ], $raw);
    }

    public function topProductsThisMonth(int $limit = 10): array
    {
        return $this->createQueryBuilder('oi')
            ->select('p.id, p.name, SUM(oi.quantity) AS sold, SUM(oi.quantity * oi.unitPrice) AS revenue')
            ->join('oi.order', 'o')
            ->join('oi.product', 'p')
            ->where('MONTH(o.createdAt) = :month AND YEAR(o.createdAt) = :year')
            ->setParameters(['month' => (int)date('n'), 'year' => (int)date('Y')])
            ->groupBy('p.id')
            ->orderBy('sold', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();
    }

    public function salesByCategoryThisMonth(): array
    {
        return $this->createQueryBuilder('oi')
            ->select('c.name AS category, SUM(oi.quantity) AS total')
            ->join('oi.order', 'o')
            ->join('oi.product', 'p')
            ->join('p.category', 'c')
            ->where('MONTH(o.createdAt) = :month AND YEAR(o.createdAt) = :year')
            ->setParameters(['month' => (int)date('n'), 'year' => (int)date('Y')])
            ->groupBy('c.id')
            ->orderBy('total', 'DESC')
            ->getQuery()->getResult();
    }
}