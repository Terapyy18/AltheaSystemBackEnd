<?php

namespace App\Entity;

use App\Repository\ProductCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    normalizationContext: ['groups' => ['category:read']],
    denormalizationContext: ['groups' => ['category:write']],
)]
#[ORM\Entity(repositoryClass: ProductCategoryRepository::class)]
class ProductCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['product:read', 'category:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['product:read', 'category:read', 'category:write'])]
    #[Assert\NotNull]
    private ?bool $status = true;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\ManyToMany(targetEntity: Product::class, mappedBy: 'productCategories')]
    private Collection $products;

    /**
     * @var Collection<int, ProductCategoryTranslation>
     */
    #[ORM\OneToMany(targetEntity: ProductCategoryTranslation::class, mappedBy: 'productCategory', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['product:read', 'category:read', 'category:write'])]
    #[Assert\Valid]
    #[Assert\Count(min: 1, minMessage: 'Une catégorie doit avoir au moins une traduction')]
    private Collection $productCategoryTranslations;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->productCategoryTranslations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): static
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->addProductCategory($this);
        }
        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            $product->removeProductCategory($this);
        }
        return $this;
    }

    /**
     * @return Collection<int, ProductCategoryTranslation>
     */
    public function getProductCategoryTranslations(): Collection
    {
        return $this->productCategoryTranslations;
    }

    public function addProductCategoryTranslation(ProductCategoryTranslation $productCategoryTranslation): static
    {
        if (!$this->productCategoryTranslations->contains($productCategoryTranslation)) {
            $this->productCategoryTranslations->add($productCategoryTranslation);
            $productCategoryTranslation->setProductCategory($this);
        }
        return $this;
    }

    public function removeProductCategoryTranslation(ProductCategoryTranslation $productCategoryTranslation): static
    {
        if ($this->productCategoryTranslations->removeElement($productCategoryTranslation)) {
            if ($productCategoryTranslation->getProductCategory() === $this) {
                $productCategoryTranslation->setProductCategory(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        if (!$this->productCategoryTranslations->isEmpty()) {
            return $this->productCategoryTranslations->first()->getTitle() ?? 'Sans nom';
        }

        return 'Catégorie #' . $this->id;
    }
}