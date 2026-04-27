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

            
            ImageField::new('imageUrl', 'Aperçu')
                ->setBasePath('')
                ->hideOnForm(),

            
            UrlField::new('imageUrl', 'URL de l\'image')
                ->onlyOnForms()
                ->setRequired(true)
                ->setHelp('Entrez l\'URL complète (ex: https://...)'),

            
            AssociationField::new('product', 'Produit associé')
                ->hideOnForm(),
        ];
    }
}