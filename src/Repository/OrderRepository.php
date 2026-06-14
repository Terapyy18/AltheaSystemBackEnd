<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    private function getMonthRange(int $month, int $year): array
    {
        $start = new \DateTime("$year-$month-01 00:00:00");
        $end   = new \DateTime($start->format('Y-m-t') . ' 23:59:59');
        return [$start, $end];
    }

    public function countThisMonth(int $month, int $year): int
    {
        [$start, $end] = $this->getMonthRange($month, $year);

        return (int) $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->where('o.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return Order[]
     */
    public function findPaidOrdersWithoutInvoice(): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.status IN (:statuses)')
            ->andWhere('o.invoicePath IS NULL')
            ->setParameter('statuses', ['paid', 'suspicious'])
            ->orderBy('o.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function ordersLast7Days(): array
    {
        $start = new \DateTime('-6 days midnight');

        $raw = $this->createQueryBuilder('o')
            ->select('o.createdAt AS date, COUNT(o.id) AS total')
            ->where('o.createdAt >= :start')
            ->setParameter('start', $start)
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->getQuery()
            ->getResult();

        // Regrouper par jour côté PHP
        $grouped = [];
        for ($i = 6; $i >= 0; $i--) {
            $key = (new \DateTime("-$i days"))->format('Y-m-d');
            $grouped[$key] = 0;
        }

        foreach ($raw as $row) {
            /** @var \DateTimeInterface $date */
            $date = $row['date'];
            $key  = $date->format('Y-m-d');
            if (isset($grouped[$key])) {
                $grouped[$key] += (int) $row['total'];
            }
        }

        $days = ['Sun' => 'Dim', 'Mon' => 'Lun', 'Tue' => 'Mar',
                 'Wed' => 'Mer', 'Thu' => 'Jeu', 'Fri' => 'Ven', 'Sat' => 'Sam'];

        return array_map(function (string $key, int $total) use ($days) {
            $label = $days[(new \DateTime($key))->format('D')] ?? (new \DateTime($key))->format('D');
            return ['label' => $label, 'total' => $total];
        }, array_keys($grouped), array_values($grouped));
    }
}