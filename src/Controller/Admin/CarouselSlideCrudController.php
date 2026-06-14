<?php

namespace App\Controller\Admin;

use App\Entity\CarouselSlide;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CarouselSlideCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CarouselSlide::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Slide du carrousel')
            ->setEntityLabelInPlural('Carrousel d\'accueil')
            ->setDefaultSort(['position' => 'ASC'])
            ->showEntityActionsInlined();
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();

        yield IntegerField::new('position', 'Ordre')
            ->setHelp('Plus la valeur est basse, plus le slide apparaît tôt dans le carrousel.');

        yield TextField::new('title', 'Titre');

        yield TextField::new('subtitle', 'Sous-titre')
            ->setRequired(false)
            ->hideOnIndex();

        yield TextField::new('ctaText', 'Texte du bouton')
            ->setRequired(false)
            ->hideOnIndex();

        yield TextField::new('ctaUrl', 'Lien du bouton')
            ->setRequired(false)
            ->setHelp('Chemin relatif (ex. /catalogue/all) ou URL complète.')
            ->hideOnIndex();

        yield ImageField::new('imageFilename', 'Image de fond')
            ->setUploadDir('public/uploads/carousel/')
            ->setBasePath('/uploads/carousel/')
            ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
            ->setRequired(false);

        yield AssociationField::new('product', 'Produit mis en avant')
            ->setRequired(false)
            ->autocomplete()
            ->setHelp('Optionnel : si renseigné et qu\'aucun lien de bouton n\'est défini, le bouton pointe vers la fiche de ce produit.')
            ->hideOnIndex();

        yield BooleanField::new('isActive', 'Actif');
    }
}
