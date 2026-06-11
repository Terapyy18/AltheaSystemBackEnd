<?php

namespace App\Controller\Admin;

use App\Entity\SiteSettings;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SiteSettingsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SiteSettings::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Paramètre du site')
            ->setEntityLabelInPlural('Paramètres du site')
            ->setDefaultSort(['settingKey' => 'ASC'])
            ->showEntityActionsInlined();
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield TextField::new('settingKey', 'Clé')
            ->setDisabled($pageName === Crud::PAGE_EDIT)
            ->setHelp('Identifiant technique non modifiable après création');
        yield TextareaField::new('valueFr', 'Texte (FR)')
            ->setNumOfRows(4);
        yield TextareaField::new('valueEn', 'Texte (EN)')
            ->setNumOfRows(4);
        yield TextField::new('description', 'Description (usage admin)')
            ->setRequired(false)
            ->hideOnIndex();
    }
}
