<?php

namespace App\Entity;

use App\Repository\CarouselSlideRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * Slide du carrousel "hero" de la page d'accueil, géré depuis EasyAdmin.
 *
 * La collection publique ne renvoie que les slides actifs, triés par position
 * croissante. Le filtre isActive=true est appliqué par CarouselSlideExtension
 * (extension Doctrine API Platform) — il ne s'applique qu'aux opérations API,
 * pas aux écrans EasyAdmin qui doivent voir tous les slides.
 */
#[ApiResource(
    operations: [
        new GetCollection(security: 'is_granted("PUBLIC_ACCESS")'),
    ],
    order: ['position' => 'ASC'],
    normalizationContext: ['groups' => ['carousel:read']],
)]
#[ORM\Entity(repositoryClass: CarouselSlideRepository::class)]
class CarouselSlide
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['carousel:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['carousel:read'])]
    private string $title;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['carousel:read'])]
    private ?string $subtitle = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['carousel:read'])]
    private ?string $ctaText = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['carousel:read'])]
    private ?string $ctaUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['carousel:read'])]
    private ?string $imageFilename = null;

    #[ORM\Column(options: ['default' => 0])]
    #[Groups(['carousel:read'])]
    private int $position = 0;

    #[ORM\Column(options: ['default' => true])]
    #[Groups(['carousel:read'])]
    private bool $isActive = true;

    /**
     * Produit mis en avant par ce slide (optionnel). Sélectionnable depuis EasyAdmin.
     * Quand il est défini et qu'aucun ctaUrl n'est renseigné, le frontend pointe
     * le CTA vers la fiche produit.
     */
    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Product $product = null;

    public function getId(): ?int { return $this->id; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }

    public function getSubtitle(): ?string { return $this->subtitle; }
    public function setSubtitle(?string $subtitle): static { $this->subtitle = $subtitle; return $this; }

    public function getCtaText(): ?string { return $this->ctaText; }
    public function setCtaText(?string $ctaText): static { $this->ctaText = $ctaText; return $this; }

    public function getCtaUrl(): ?string { return $this->ctaUrl; }
    public function setCtaUrl(?string $ctaUrl): static { $this->ctaUrl = $ctaUrl; return $this; }

    public function getImageFilename(): ?string { return $this->imageFilename; }
    public function setImageFilename(?string $imageFilename): static { $this->imageFilename = $imageFilename; return $this; }

    public function getPosition(): int { return $this->position; }
    public function setPosition(int $position): static { $this->position = $position; return $this; }

    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): static { $this->isActive = $isActive; return $this; }

    public function getProduct(): ?Product { return $this->product; }
    public function setProduct(?Product $product): static { $this->product = $product; return $this; }

    /** ID du produit lié, exposé à l'API pour construire le lien vers la fiche produit. */
    #[Groups(['carousel:read'])]
    public function getProductId(): ?int { return $this->product?->getId(); }

    /**
     * Image (thumbnail) du produit lié, exposée pour servir d'image de fond du slide
     * lorsqu'aucune image n'a été uploadée explicitement (imageFilename vide).
     */
    #[Groups(['carousel:read'])]
    public function getProductThumbnail(): ?string { return $this->product?->getThumbnail(); }

    /**
     * Titres traduits (indexés par langue : ['fr' => ..., 'en' => ...]) du produit lié.
     * Le frontend les utilise pour afficher le nom du produit comme titre du slide.
     */
    #[Groups(['carousel:read'])]
    public function getProductTitles(): array
    {
        if (!$this->product) {
            return [];
        }
        $titles = [];
        foreach ($this->product->getProductTranslations() as $translation) {
            $titles[$translation->getLanguage()] = $translation->getTitle();
        }
        return $titles;
    }

    public function __toString(): string { return $this->title ?? 'Slide'; }
}
