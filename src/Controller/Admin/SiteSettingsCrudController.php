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
            ->onlyWhenCreating();
        yield TextareaField::new('valueFr', 'Texte (FR)')
            ->setNumOfRows(4);
        yield TextareaField::new('valueEn', 'Texte (EN)')
            ->setNumOfRows(4);
        yield TextareaField::new('valueAr', 'Texte (AR — arabe, RTL)')
            ->setRequired(false)
            ->setNumOfRows(4)
            ->setHelp('Langue droite-à-gauche. Laisser vide si non traduit.')
            ->hideOnIndex();
        yield TextareaField::new('valueZh', 'Texte (ZH — chinois)')
            ->setRequired(false)
            ->setNumOfRows(4)
            ->setHelp('Laisser vide si non traduit.')
            ->hideOnIndex();
    }
}
