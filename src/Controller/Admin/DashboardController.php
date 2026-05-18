<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Controller\Admin\AddressesCrudController;
use App\Controller\Admin\UserCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(UserCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('AltheaSystemBackEnd');
    }



    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');

        yield MenuItem::section('Utilisateurs');
        yield MenuItem::linkTo(UserCrudController::class, 'Utilisateurs', 'fas fa-users')->setAction('index');
        yield MenuItem::linkTo(AddressesCrudController::class, 'Adresses', 'fas fa-map-marker-alt')->setAction('index');

        yield MenuItem::section('Catalogue');
        yield MenuItem::linkTo(ProductCrudController::class, 'Produits', 'fas fa-box')->setAction('index');
        yield MenuItem::linkTo(ProductCategoryCrudController::class, 'Catégories', 'fas fa-tags')->setAction('index');
        yield MenuItem::linkTo(ProductTranslationCrudController::class, 'Traductions Produits', 'fas fa-language')->setAction('index');
        yield MenuItem::linkTo(ProductCategoryTranslationCrudController::class, 'Traductions Catégories', 'fas fa-globe')->setAction('index');
        yield MenuItem::linkTo(ProductImagesCrudController::class, 'Galerie Photos', 'fas fa-images')->setAction('index');

        yield MenuItem::section('Ventes');
        yield MenuItem::linkTo(OrderCrudController::class, 'Commandes', 'fas fa-shopping-bag')->setAction('index');
        yield MenuItem::linkTo(ItemsOrderCrudController::class, 'Lignes de Commande', 'fas fa-list')->setAction('index');

        yield MenuItem::section('Assistance');
        yield MenuItem::linkTo(SupportCrudController::class, 'Support Client', 'fas fa-headset')->setAction('index');

        yield MenuItem::linkToRoute('Statistiques', 'fa fa-chart-bar', 'admin_stats');

    }
}