<?php

namespace App\Controller\Admin;

use App\Entity\ItemsOrder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class ItemsOrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ItemsOrder::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            
            AssociationField::new('order', 'Commande'),

            AssociationField::new('product', 'Produit'),

            IntegerField::new('quantity', 'Quantité'),

            MoneyField::new('price', 'Prix Unitaire')
                ->setCurrency('EUR')
                ->setStoredAsCents(false),
        ];
    }
}