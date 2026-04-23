<?php

namespace App\Controller\Admin;

use App\Entity\ProductImages;
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

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),

            // Aperçu visuel en index/détail uniquement
            ImageField::new('imageUrl', 'Aperçu')
                ->setBasePath('')
                ->hideOnForm(),

            // Saisie URL dans le formulaire
            UrlField::new('imageUrl', 'URL de l\'image')
                ->onlyOnForms()
                ->setRequired(true)
                ->setHelp('Entrez l\'URL complète (ex: https://...)'),

            // Masquer l'association quand utilisé en sous-formulaire depuis Product
            AssociationField::new('product', 'Produit associé')
                ->hideOnForm(),
        ];
    }
}