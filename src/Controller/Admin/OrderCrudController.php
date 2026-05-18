<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;

class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Commande')
            ->setEntityLabelInPlural('Commandes')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPageTitle('index', 'Liste des Commandes')
            ->setPageTitle('edit', 'Modifier la Commande')
            ->setPageTitle('detail', 'Détail de la Commande');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)       // pas de création manuelle
            ->disable(Action::DELETE)    // pas de suppression
            ->add(Crud::PAGE_INDEX, Action::DETAIL)  // bouton "Voir"
            ->add(Crud::PAGE_EDIT, Action::DETAIL);  // bouton "Voir" depuis l'édition
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
                    'Payée'      => 'paid',
                    'Expédiée'   => 'shipped',
                    'Livrée'     => 'received',
                    'Annulée'    => 'cancelled',
                ])
                ->renderAsBadges([
                    'pending'   => 'warning',
                    'paid'      => 'info',
                    'shipped'   => 'primary',
                    'received'  => 'success',
                    'cancelled' => 'danger',
                ]),

            NumberField::new('totalPrice', 'Montant Total (€)')
                ->setNumDecimals(2)
                ->setFormTypeOption('disabled', true), // lecture seule en édition

            IntegerField::new('shippingNumber', 'N° Suivi Colis'),

            TextField::new('invoicePath', 'Lien Facture (PDF)')
                ->hideOnIndex(),

            DateTimeField::new('createdAt', 'Créée le')
                ->hideOnForm(),
            DateTimeField::new('payedAt', 'Payée le')
                ->onlyOnDetail(),
            DateTimeField::new('shippedAt', 'Expédiée le')
                ->onlyOnDetail(),
            DateTimeField::new('receivedAt', 'Reçue le')
                ->onlyOnDetail(),

            CollectionField::new('itemsOrders', 'Produits commandés')
                ->useEntryCrudForm(ItemsOrderCrudController::class)
                ->onlyOnDetail(),
        ];
    }
}