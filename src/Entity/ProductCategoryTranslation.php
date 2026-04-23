<?php

namespace App\Entity;

use App\Repository\ProductCategoryTranslationRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource]
#[ORM\Entity(repositoryClass: ProductCategoryTranslationRepository::class)]
class ProductCategoryTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['product:read', 'category:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['product:read', 'category:read', 'category:write'])]
    #[Assert\NotBlank]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Groups(['product:read', 'category:read', 'category:write'])]
    #[Assert\NotBlank]
    private ?string $description = null;

    #[ORM\Column(length: 10)]
    #[Groups(['product:read', 'category:read', 'category:write'])]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['fr', 'en', 'es', 'de'], message: 'La langue doit être fr, en, es ou de')]
    private ?string $language = null;

    #[ORM\ManyToOne(inversedBy: 'productCategoryTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProductCategory $productCategory = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(string $language): static
    {
        $this->language = $language;
        return $this;
    }

    public function getProductCategory(): ?ProductCategory
    {
        return $this->productCategory;
    }

    public function setProductCategory(?ProductCategory $productCategory): static
    {
        $this->productCategory = $productCategory;
        return $this;
    }

    public function __toString(): string
    {
        return sprintf('%s [%s]', $this->title, strtoupper($this->language ?? '??'));
    }
}