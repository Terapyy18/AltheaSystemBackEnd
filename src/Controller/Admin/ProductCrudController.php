<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Produit')
            ->setEntityLabelInPlural('Produits')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPageTitle('index', 'Liste des Produits')
            ->setPageTitle('new', 'Créer un Produit')
            ->setPageTitle('edit', 'Modifier le Produit');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            
            IdField::new('id')
                ->hideOnForm(),

            TextField::new('sku', 'Référence (SKU)'),

            BooleanField::new('is_published', 'Publié')
                ->renderAsSwitch(true),

            IntegerField::new('priority', 'Priorité d\'affichage')
                ->setHelp('Plus le chiffre est élevé, plus le produit apparaît en premier'),

            
            NumberField::new('price', 'Prix HT (€)')
                ->setNumDecimals(2),

            NumberField::new('promo_price', 'Prix Promo (€)')
                ->setNumDecimals(2)
                ->setHelp('Laisser vide si pas de promotion'),

            IntegerField::new('stock', 'Stock'),


            NumberField::new('weight', 'Poids (kg)')
                ->setNumDecimals(3)
                ->hideOnIndex(),

            NumberField::new('height', 'Hauteur (cm)')
                ->setNumDecimals(2)
                ->hideOnIndex(),

            NumberField::new('length', 'Longueur (cm)')
                ->setNumDecimals(2)
                ->hideOnIndex(),

            
            
            ImageField::new('thumbnail', 'Aperçu')
                ->setBasePath('')          
                ->hideOnForm(),

            
            UrlField::new('thumbnail', 'URL Image principale')
                ->onlyOnForms()
                ->setHelp('Entrez l\'URL complète de l\'image (ex: https://...)'),

            
            AssociationField::new('productCategories', 'Catégories')
                ->setFormTypeOption('by_reference', false),

            
            DateTimeField::new('create_at', 'Créé le')
                ->hideOnForm()
                ->hideOnIndex(),

            
            CollectionField::new('productTranslations', 'Traductions')
                ->useEntryCrudForm(ProductTranslationCrudController::class)
                ->setFormTypeOption('by_reference', false)
                ->setHelp('⚠️ Ajoutez au moins une traduction en Français pour valider')
                ->hideOnIndex(),

            
            CollectionField::new('productImages', 'Galerie Photos')
                ->useEntryCrudForm(ProductImagesCrudController::class)
                ->setFormTypeOption('by_reference', false)
                ->hideOnIndex(),
        ];
    }
}