<?php

namespace App\Controller\Admin;

use App\Entity\ProductCategoryTranslation;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;

class ProductCategoryTranslationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductCategoryTranslation::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            
            ChoiceField::new('language', 'Langue')
                ->setChoices([
                    'Français' => 'fr',
                    'English' => 'en',
                    'Español' => 'es',
                    'Deutsch' => 'de',
                ]),

            TextField::new('title', 'Titre de la catégorie'),
            
            
            TextField::new('description', 'Description'),
        ];
    }
}