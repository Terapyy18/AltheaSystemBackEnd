<?php

namespace App\Controller\Admin;

use App\Entity\ProductTranslation;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;

class ProductTranslationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductTranslation::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            
            ChoiceField::new('language', 'Langue')
                ->setChoices([
                    'Français' => 'fr',
                    'English' => 'en',
                ])
                ->renderAsBadges(),

            TextField::new('title', 'Nom du produit'),
            TextField::new('subtitle', 'Sous-titre'),
            
            TextField::new('description', 'Description complète'),
            TextField::new('composition', 'Composition / Ingrédients'),
        ];
    }
}