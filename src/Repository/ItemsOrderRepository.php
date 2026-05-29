<?php

namespace App\Repository;

use App\Entity\ItemsOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ItemsOrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ItemsOrder::class);
    }

    private function getMonthRange(int $month, int $year): array
    {
        $start = new \DateTime("$year-$month-01 00:00:00");
        $end   = new \DateTime($start->format('Y-m-t') . ' 23:59:59');
        return [$start, $end];
    }

    public function totalSoldThisMonth(int $month, int $year): int
    {
        [$start, $end] = $this->getMonthRange($month, $year);

        return (int) $this->createQueryBuilder('oi')
            ->select('SUM(oi.quantity)')
            ->join('oi.order', 'o')
            ->where('o.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function revenueThisMonth(int $month, int $year): float
    {
        [$start, $end] = $this->getMonthRange($month, $year);

        return (float) $this->createQueryBuilder('oi')
            ->select('SUM(oi.quantity * oi.price)')
            ->join('oi.order', 'o')
            ->where('o.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function salesLast12Months(): array
    {
        $start = new \DateTime('first day of this month -11 months 00:00:00');

        $raw = $this->createQueryBuilder('oi')
            ->select('o.createdAt AS date, SUM(oi.quantity) AS total')
            ->join('oi.order', 'o')
            ->where('o.createdAt >= :start')
            ->setParameter('start', $start)
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->getQuery()
            ->getResult();

        // Regrouper par mois côté PHP
        $grouped = [];
        $monthLabels = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];

        foreach ($raw as $row) {
            /** @var \DateTimeInterface $date */
            $date = $row['date'];
            $key  = $date->format('Y-m');
            $grouped[$key] = ($grouped[$key] ?? 0) + (int) $row['total'];
        }

        ksort($grouped);

        return array_map(function (string $key, int $total) use ($monthLabels) {
            [$y, $m] = explode('-', $key);
            return [
                'label' => $monthLabels[(int)$m - 1] . ' ' . $y,
                'total' => $total,
            ];
        }, array_keys($grouped), array_values($grouped));
    }

    public function topProductsThisMonth(int $limit = 10): array
    {
        [$start, $end] = $this->getMonthRange((int)date('n'), (int)date('Y'));

        return $this->createQueryBuilder('oi')
            ->select('p.id, p.sku, pt.title AS name, SUM(oi.quantity) AS sold, SUM(oi.quantity * oi.price) AS revenue')
            ->join('oi.order', 'o')
            ->join('oi.product', 'p')
            ->leftJoin('p.productTranslations', 'pt', 'WITH', 'pt.language = :lang')
            ->setParameter('lang', 'fr')
            ->where('o.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->groupBy('p.id, pt.title')
            ->orderBy('sold', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function salesByCategoryThisMonth(): array
    {
        [$start, $end] = $this->getMonthRange((int)date('n'), (int)date('Y'));

        return $this->createQueryBuilder('oi')
            ->select('ct.title AS category, SUM(oi.quantity) AS total')
            ->join('oi.order', 'o')
            ->join('oi.product', 'p')
            ->join('p.productCategories', 'c')
            ->leftJoin('c.productCategoryTranslations', 'ct', 'WITH', 'ct.language = :lang')
            ->setParameter('lang', 'fr')
            ->where('o.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->groupBy('c.id, ct.title')
            ->orderBy('total', 'DESC')
            ->getQuery()
            ->getResult();
    }
}