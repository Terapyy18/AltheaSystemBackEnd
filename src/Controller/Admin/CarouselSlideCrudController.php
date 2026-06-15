<?php

namespace App\Controller\Admin;

use App\Entity\CarouselSlide;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

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

        yield AssociationField::new('product', 'Produit mis en avant')
            ->setRequired(true)
            ->autocomplete()
            ->setHelp('Le slide affiche le nom et l\'image de ce produit ; le bouton pointe vers sa fiche.');

        yield IntegerField::new('position', 'Ordre')
            ->setHelp('Plus la valeur est basse, plus le slide apparaît tôt dans le carrousel.');

        yield BooleanField::new('isActive', 'Actif');
    }

    /**
     * Le titre du slide n'est plus saisi à la main : on le synchronise sur le nom
     * du produit (traduction FR en priorité) pour satisfaire la contrainte NOT NULL
     * et garder un libellé lisible dans EasyAdmin.
     */
    private function syncTitleFromProduct(CarouselSlide $slide): void
    {
        $product = $slide->getProduct();
        if (!$product instanceof Product) {
            return;
        }

        $title = null;
        $fallback = null;
        foreach ($product->getProductTranslations() as $translation) {
            $fallback ??= $translation->getTitle();
            if ($translation->getLanguage() === 'fr') {
                $title = $translation->getTitle();
                break;
            }
        }

        $slide->setTitle($title ?? $fallback ?? 'Slide');
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof CarouselSlide) {
            $this->syncTitleFromProduct($entityInstance);
        }
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof CarouselSlide) {
            $this->syncTitleFromProduct($entityInstance);
        }
        parent::updateEntity($entityManager, $entityInstance);
    }
}
