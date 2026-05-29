<?php

namespace App\Controller\Admin;

use App\Entity\ProductImages;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class ProductImagesCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductImages::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)
            ->disable(Action::DELETE)
            ->disable(Action::EDIT)                         // Lecture seule uniquement
            ->add(Crud::PAGE_INDEX, Action::DETAIL);        // Ajoute le bouton "Voir" (œil) sur la liste
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),

            // Aperçu visuel de l'image (Index et Page Détail)
            ImageField::new('imageUrl', 'Aperçu')
                ->setBasePath('')
                ->hideOnForm(),

            // URL brute au format texte cliquable (Page Détail uniquement)
            UrlField::new('imageUrl', 'URL de l\'image')
                ->onlyOnDetail(),

            // Relation avec le produit associé (Index et Page Détail)
            AssociationField::new('product', 'Produit associé'),
        ];
    }
}