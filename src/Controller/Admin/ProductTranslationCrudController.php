<?php

namespace App\Controller\Admin;

use App\Entity\ProductTranslation;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProductTranslationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductTranslation::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)
            ->disable(Action::DELETE)
            ->disable(Action::EDIT)                         // Lecture seule uniquement
            ->add(Crud::PAGE_INDEX, Action::DETAIL);        // Ajoute le bouton "Voir" (œil)
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            
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