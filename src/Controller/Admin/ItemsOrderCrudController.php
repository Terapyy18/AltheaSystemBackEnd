<?php

namespace App\Controller\Admin;

use App\Entity\ItemsOrder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;

class ItemsOrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ItemsOrder::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Ligne de Commande')
            ->setEntityLabelInPlural('Lignes de Commande')
            ->setPageTitle('index', 'Lignes de Commande')
            ->setPageTitle('detail', 'Détail de la Ligne');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)
            ->disable(Action::DELETE)
            ->disable(Action::EDIT)                      // lecture seule uniquement
            ->add(Crud::PAGE_INDEX, Action::DETAIL);     // bouton "Voir"
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),

            AssociationField::new('order', 'Commande'),

            AssociationField::new('product', 'Produit'),

            IntegerField::new('quantity', 'Quantité'),

            NumberField::new('price', 'Prix Unitaire (€)')
                ->setNumDecimals(2),
        ];
    }
}