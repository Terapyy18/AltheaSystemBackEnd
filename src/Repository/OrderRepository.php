<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function countThisMonth(int $month, int $year): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->where('MONTH(o.createdAt) = :month AND YEAR(o.createdAt) = :year')
            ->setParameters(['month' => $month, 'year' => $year])
            ->getQuery()->getSingleScalarResult();
    }

    public function ordersLast7Days(): array
    {
        $raw = $this->createQueryBuilder('o')
            ->select('DATE(o.createdAt) AS day, COUNT(o.id) AS total')
            ->where('o.createdAt >= :start')
            ->setParameter('start', new \DateTime('-7 days'))
            ->groupBy('day')
            ->orderBy('day', 'ASC')
            ->getQuery()->getResult();

        return array_map(fn($r) => [
            'label' => (new \DateTime($r['day']))->format('D'),
            'total' => (int) $r['total'],
        ], $raw);
    }
}