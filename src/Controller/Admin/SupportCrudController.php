<?php

namespace App\Controller\Admin;

use App\Entity\Support;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class SupportCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Support::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Ticket Support')
            ->setEntityLabelInPlural('Tickets Support')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPageTitle('index', 'Liste des Tickets')
            ->setPageTitle('new', 'Créer un Ticket')
            ->setPageTitle('edit', 'Répondre au Ticket');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),

            TextField::new('title', 'Titre'),

            ChoiceField::new('type', 'Type')
                ->setChoices([
                    'Question'     => 'question',
                    'Réclamation'  => 'reclamation',
                    'Bug'          => 'bug',
                    'Autre'        => 'autre',
                ]),

            ChoiceField::new('status', 'Statut')
                ->setChoices([
                    'Ouvert'      => 'ouvert',
                    'En cours'    => 'en_cours',
                    'Résolu'      => 'resolu',
                    'Fermé'       => 'ferme',
                ])
                ->renderAsBadges([
                    'ouvert'   => 'warning',
                    'en_cours' => 'primary',
                    'resolu'   => 'success',
                    'ferme'    => 'secondary',
                ]),

            AssociationField::new('user', 'Utilisateur'),

            TextareaField::new('message', 'Message client')
                ->setRequired(true)
                ->hideOnIndex(),

            
            TextareaField::new('reply', 'Réponse admin')
                ->setRequired(false)
                ->hideOnIndex()
                ->setHelp('Laissez vide si pas encore de réponse'),
        ];
    }
}