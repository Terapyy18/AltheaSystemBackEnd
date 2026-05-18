<?php
namespace App\Controller\Admin;

use App\Repository\ItemsOrderRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardStatsController extends AbstractController
{
    public function __construct(
        private ItemsOrderRepository $orderItemRepo,
        private OrderRepository $orderRepo,
        private ProductRepository $productRepo,
    ) {}

    #[Route('/admin/stats', name: 'admin_stats')]
    public function index(): Response
    {
        $month = (int) date('n');
        $year  = (int) date('Y');

        return $this->render('stats/dashboard_stats.html.twig', [
            'kpis'         => [
                'total_sold' => $this->orderItemRepo->totalSoldThisMonth($month, $year),
                'revenue'    => $this->orderItemRepo->revenueThisMonth($month, $year),
                'orders'     => $this->orderRepo->countThisMonth($month, $year),
            ],
            'monthly'      => $this->orderItemRepo->salesLast12Months(),
            'top_products' => $this->orderItemRepo->topProductsThisMonth(5),
            'categories'   => $this->orderItemRepo->salesByCategoryThisMonth(),
            'daily'        => $this->orderRepo->ordersLast7Days(),
            'critical'     => $this->productRepo->criticalStock(),
        ]);
    }
}