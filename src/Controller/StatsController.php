<?php
// src/Controller/Api/StatsController.php
namespace App\Controller\Api;

use App\Repository\OrderItemRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/stats', name: 'api_stats_')]
class StatsController extends AbstractController
{
    public function __construct(
        private OrderItemRepository $orderItemRepo,
        private OrderRepository $orderRepo,
        private ProductRepository $productRepo,
    ) {}

    // KPIs globaux du mois courant
    #[Route('/kpis', name: 'kpis', methods: ['GET'])]
    public function kpis(): JsonResponse
    {
        $month = (int) date('n');
        $year  = (int) date('Y');

        $totalSold     = $this->orderItemRepo->totalSoldThisMonth($month, $year);
        $revenue       = $this->orderItemRepo->revenueThisMonth($month, $year);
        $ordersCount   = $this->orderRepo->countThisMonth($month, $year);
        $avgBasket     = $ordersCount > 0 ? round($revenue / $ordersCount, 2) : 0;

        // Comparaison mois précédent
        $prevMonth = $month === 1 ? 12 : $month - 1;
        $prevYear  = $month === 1 ? $year - 1 : $year;
        $prevSold  = $this->orderItemRepo->totalSoldThisMonth($prevMonth, $prevYear);
        $prevRev   = $this->orderItemRepo->revenueThisMonth($prevMonth, $prevYear);

        return $this->json([
            'total_sold'   => $totalSold,
            'revenue'      => $revenue,
            'orders'       => $ordersCount,
            'avg_basket'   => $avgBasket,
            'trends' => [
                'sold'    => $prevSold > 0 ? round(($totalSold - $prevSold) / $prevSold * 100) : 0,
                'revenue' => $prevRev > 0  ? round(($revenue - $prevRev)    / $prevRev  * 100) : 0,
            ],
        ]);
    }

    // Ventes mensuelles sur les 12 derniers mois
    #[Route('/sales/monthly', name: 'monthly', methods: ['GET'])]
    public function monthlySales(): JsonResponse
    {
        $data = $this->orderItemRepo->salesLast12Months();
        return $this->json($data);
    }

    // Top N produits ce mois
    #[Route('/products/top', name: 'top_products', methods: ['GET'])]
    public function topProducts(): JsonResponse
    {
        $data = $this->orderItemRepo->topProductsThisMonth(10);
        return $this->json($data);
    }

    // Ventes par catégorie ce mois
    #[Route('/categories', name: 'categories', methods: ['GET'])]
    public function byCategory(): JsonResponse
    {
        $data = $this->orderItemRepo->salesByCategoryThisMonth();
        return $this->json($data);
    }

    // Commandes par jour (7 derniers jours)
    #[Route('/orders/daily', name: 'daily_orders', methods: ['GET'])]
    public function dailyOrders(): JsonResponse
    {
        $data = $this->orderRepo->ordersLast7Days();
        return $this->json($data);
    }

    // Produits en stock critique
    #[Route('/stock/critical', name: 'stock_critical', methods: ['GET'])]
    public function criticalStock(): JsonResponse
    {
        $data = $this->productRepo->criticalStock();
        return $this->json($data);
    }
}