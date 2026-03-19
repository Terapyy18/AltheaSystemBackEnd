<?php

namespace App\Entity;

use App\Repository\ProductCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource]
#[ORM\Entity(repositoryClass: ProductCategoryRepository::class)]
class ProductCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['product:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['product:read'])]
    private ?bool $status = null;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\ManyToMany(targetEntity: Product::class, inversedBy: 'productCategories')]
    private Collection $product;

    /**
     * @var Collection<int, ProductCategoryTranslation>
     */
    #[ORM\OneToMany(targetEntity: ProductCategoryTranslation::class, mappedBy: 'productCategory')]
    #[Groups(['product:read'])]
    private Collection $productCategoryTranslation;

    public function __construct()
    {
        $this->product = new ArrayCollection();
        $this->productCategoryTranslation = new ArrayCollection();
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
    public function getProduct(): Collection
    {
        return $this->product;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->product->contains($product)) {
            $this->product->add($product);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        $this->product->removeElement($product);

        return $this;
    }

    /**
     * @return Collection<int, ProductCategoryTranslation>
     */
    public function getProductCategoryTranslation(): Collection
    {
        return $this->productCategoryTranslation;
    }

    public function addProductCategoryTranslation(ProductCategoryTranslation $productCategoryTranslation): static
    {
        if (!$this->productCategoryTranslation->contains($productCategoryTranslation)) {
            $this->productCategoryTranslation->add($productCategoryTranslation);
            $productCategoryTranslation->setProductCategory($this);
        }

        return $this;
    }

    public function removeProductCategoryTranslation(ProductCategoryTranslation $productCategoryTranslation): static
    {
        if ($this->productCategoryTranslation->removeElement($productCategoryTranslation)) {
            // set the owning side to null (unless already changed)
            if ($productCategoryTranslation->getProductCategory() === $this) {
                $productCategoryTranslation->setProductCategory(null);
            }
        }

        return $this;
    }
}
