<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['product:read']]),
        new GetCollection(normalizationContext: ['groups' => ['product:read']]),
        new Post(
            security: 'is_granted("ROLE_ADMIN")',
            denormalizationContext: ['groups' => ['product:write']],
        ),
        new Put(
            security: 'is_granted("ROLE_ADMIN")',
            denormalizationContext: ['groups' => ['product:write']],
        ),
        new Delete(
            security: 'is_granted("ROLE_ADMIN")',
        ),
    ],
    normalizationContext: ['groups' => ['product:read']],
    denormalizationContext: ['groups' => ['product:write']],
)]
#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['product:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['product:read', 'product:write'])]
    #[Assert\NotNull]
    private ?bool $is_published = false;

    #[ORM\Column(length: 255)]
    #[Groups(['product:read', 'product:write'])]
    #[Assert\NotBlank]
    private ?string $thumbnail = null;

    #[ORM\Column]
    #[Groups(['product:read', 'product:write'])]
    #[Assert\NotNull]
    #[Assert\Positive]
    private ?float $weight = null;

    #[ORM\Column]
    #[Groups(['product:read', 'product:write'])]
    #[Assert\NotNull]
    #[Assert\Positive]
    private ?float $height = null;

    #[ORM\Column]
    #[Groups(['product:read', 'product:write'])]
    #[Assert\NotNull]
    #[Assert\Positive]
    private ?float $length = null;

    #[ORM\Column]
    #[Groups(['product:read', 'product:write'])]
    #[Assert\NotNull]
    #[Assert\Positive]
    private ?float $price = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['product:read', 'product:write'])]
    #[Assert\Positive]
    private ?float $promo_price = null;

    #[ORM\Column]
    #[Groups(['product:read'])]
    private ?\DateTimeImmutable $create_at = null;

    #[ORM\Column]
    #[Groups(['product:read', 'product:write'])]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private ?int $priority = 0;

    #[ORM\Column]
    #[Groups(['product:read', 'product:write'])]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private ?int $stock = 0;

    #[ORM\Column(length: 255)]
    #[Groups(['product:read', 'product:write'])]
    #[Assert\NotBlank]
    private ?string $sku = null;

    /**
     * @var Collection<int, ProductCategory>
     */
    #[ORM\ManyToMany(targetEntity: ProductCategory::class, inversedBy: 'products')]
    #[ORM\JoinTable(name: 'product_product_category')]
    #[Groups(['product:read', 'product:write'])]
    private Collection $productCategories;

    /**
     * @var Collection<int, ProductImages>
     */
    #[ORM\OneToMany(targetEntity: ProductImages::class, mappedBy: 'product', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['product:read', 'product:write'])]
    private Collection $productImages;

    /**
     * @var Collection<int, ProductTranslation>
     */
    #[ORM\OneToMany(targetEntity: ProductTranslation::class, mappedBy: 'product', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['product:read', 'product:write'])]
    #[Assert\Valid]
    #[Assert\Count(min: 1, minMessage: 'Un produit doit avoir au moins une traduction')]
    private Collection $productTranslations;

    /**
     * @var Collection<int, ItemsOrder>
     */
    #[ORM\OneToMany(targetEntity: ItemsOrder::class, mappedBy: 'product')]
    private Collection $itemsOrders;

    public function __construct()
    {
        $this->productCategories = new ArrayCollection();
        $this->productImages = new ArrayCollection();
        $this->productTranslations = new ArrayCollection();
        $this->itemsOrders = new ArrayCollection();
        $this->create_at = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isPublished(): ?bool
    {
        return $this->is_published;
    }

    public function setIsPublished(bool $is_published): static
    {
        $this->is_published = $is_published;
        return $this;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(string $thumbnail): static
    {
        $this->thumbnail = $thumbnail;
        return $this;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(float $weight): static
    {
        $this->weight = $weight;
        return $this;
    }

    public function getHeight(): ?float
    {
        return $this->height;
    }

    public function setHeight(float $height): static
    {
        $this->height = $height;
        return $this;
    }

    public function getLength(): ?float
    {
        return $this->length;
    }

    public function setLength(float $length): static
    {
        $this->length = $length;
        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function getPromoPrice(): ?float
    {
        return $this->promo_price;
    }

    public function setPromoPrice(?float $promo_price): static
    {
        $this->promo_price = $promo_price;
        return $this;
    }

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->create_at;
    }

    public function setCreateAt(\DateTimeImmutable $create_at): static
    {
        $this->create_at = $create_at;
        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): static
    {
        $this->priority = $priority;
        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;
        return $this;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(string $sku): static
    {
        $this->sku = $sku;
        return $this;
    }

    /**
     * @return Collection<int, ProductCategory>
     */
    public function getProductCategories(): Collection
    {
        return $this->productCategories;
    }

    public function addProductCategory(ProductCategory $productCategory): static
    {
        if (!$this->productCategories->contains($productCategory)) {
            $this->productCategories->add($productCategory);
        }
        return $this;
    }

    public function removeProductCategory(ProductCategory $productCategory): static
    {
        $this->productCategories->removeElement($productCategory);
        return $this;
    }

    /**
     * @return Collection<int, ProductImages>
     */
    public function getProductImages(): Collection
    {
        return $this->productImages;
    }

    public function addProductImage(ProductImages $productImage): static
    {
        if (!$this->productImages->contains($productImage)) {
            $this->productImages->add($productImage);
            $productImage->setProduct($this);
        }
        return $this;
    }

    public function removeProductImage(ProductImages $productImage): static
    {
        if ($this->productImages->removeElement($productImage)) {
            if ($productImage->getProduct() === $this) {
                $productImage->setProduct(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, ProductTranslation>
     */
    public function getProductTranslations(): Collection
    {
        return $this->productTranslations;
    }

    public function addProductTranslation(ProductTranslation $productTranslation): static
    {
        if (!$this->productTranslations->contains($productTranslation)) {
            $this->productTranslations->add($productTranslation);
            $productTranslation->setProduct($this);
        }
        return $this;
    }

    public function removeProductTranslation(ProductTranslation $productTranslation): static
    {
        if ($this->productTranslations->removeElement($productTranslation)) {
            if ($productTranslation->getProduct() === $this) {
                $productTranslation->setProduct(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, ItemsOrder>
     */
    public function getItemsOrders(): Collection
    {
        return $this->itemsOrders;
    }

    public function addItemsOrder(ItemsOrder $itemsOrder): static
    {
        if (!$this->itemsOrders->contains($itemsOrder)) {
            $this->itemsOrders->add($itemsOrder);
            $itemsOrder->setProduct($this);
        }
        return $this;
    }

    public function removeItemsOrder(ItemsOrder $itemsOrder): static
    {
        if ($this->itemsOrders->removeElement($itemsOrder)) {
            if ($itemsOrder->getProduct() === $this) {
                $itemsOrder->setProduct(null);
            }
        }
        return $this;
    }

    

    // src/Entity/Product.php

    public function __toString(): string
    {
        // On essaie de récupérer le SKU qui est unique et présent dans Product
        return $this->sku ?? 'Produit sans SKU';

        if (!$this->productTranslations->isEmpty()) {
            return $this->productTranslations->first()->getTitle();
        }
        return 'Produit #' . $this->id;
    }
}