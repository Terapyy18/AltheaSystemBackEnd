<?php

namespace App\Controller\Admin;

use App\Entity\Support;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class SupportCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Support::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            
            
            AssociationField::new('user', 'Client'),

            TextField::new('title', 'Sujet'),

            
            ChoiceField::new('type', 'Type')
                ->setChoices([
                    'Technique' => 'technical',
                    'Facturation' => 'billing',
                    'Autre' => 'other',
                ]),

            
            ChoiceField::new('status', 'Statut')
                ->setChoices([
                    'En attente' => 'pending',
                    'En cours' => 'processing',
                    'Résolu' => 'resolved',
                    'Fermé' => 'closed',
                ])
                ->renderAsBadges([
                    'pending' => 'warning',
                    'processing' => 'info',
                    'resolved' => 'success',
                    'closed' => 'danger',
                ]),

            TextareaField::new('message', 'Message du client')->hideOnIndex(),
            
            
            TextareaField::new('reply', 'Réponse de l\'assistance'),
        ];
    }
}