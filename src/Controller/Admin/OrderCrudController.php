<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;

class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            
            
            AssociationField::new('user', 'Client'),
            AssociationField::new('addresses', 'Adresse de livraison'),

            
            ChoiceField::new('status', 'Statut')
                ->setChoices([
                    'En attente' => 'pending',
                    'Payée' => 'paid',
                    'Expédiée' => 'shipped',
                    'Livrée' => 'received',
                    'Annulée' => 'cancelled',
                ])
                ->renderAsBadges([
                    'pending' => 'warning',
                    'paid' => 'info',
                    'shipped' => 'primary',
                    'received' => 'success',
                    'cancelled' => 'danger',
                ]),

            
            MoneyField::new('totalPrice', 'Montant Total')
                ->setCurrency('EUR')
                ->setStoredAsCents(false),


            IntegerField::new('shippingNumber', 'N° Suivi Colis'),
            TextField::new('invoicePath', 'Lien Facture (PDF)')->hideOnIndex(),


            DateTimeField::new('createdAt', 'Créée le')->hideOnForm(),
            DateTimeField::new('payedAt', 'Payée le')->onlyOnDetail(),
            DateTimeField::new('shippedAt', 'Expédiée le')->onlyOnDetail(),
            DateTimeField::new('receivedAt', 'Reçue le')->onlyOnDetail(),

            CollectionField::new('itemsOrders', 'Produits commandés')
                ->useEntryCrudForm(ItemsOrderCrudController::class)
                ->onlyOnDetail(), 
        ];
    }
}