<?php

namespace App\Entity;

use App\Repository\ItemsOrderRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(
            security: 'is_granted("ROLE_USER")',
            normalizationContext: ['groups' => ['order:read']],
        ),
        new Get(
            security: 'is_granted("ROLE_ADMIN") or object.getOrder().getUser() == user',
            normalizationContext: ['groups' => ['order:read']],
        ),
        new Post(
            security: 'is_granted("ROLE_USER")',
            normalizationContext: ['groups' => ['order:read']],
        ),
        new Delete(
            security: 'is_granted("ROLE_ADMIN")',
        ),
    ],
)]
#[ORM\Entity(repositoryClass: ItemsOrderRepository::class)]
class ItemsOrder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['order:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['order:read'])]
    private ?int $quantity = null;

    #[ORM\Column]
    #[Groups(['order:read'])]
    private ?float $price = null;

    // Correction : inversedBy doit correspondre à la propriété dans Product.php
    // Si tu n'as pas de collection d'ItemsOrder dans Product, retire carrément le inversedBy.
    #[ORM\ManyToOne] 
    #[Groups(['order:read'])]
    private ?Product $product = null;

    #[ORM\ManyToOne(inversedBy: 'itemsOrders')]
    #[ORM\JoinColumn(name: 'idorder_id', referencedColumnName: 'id', nullable: false)]
    private ?Order $order = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
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

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;
        return $this;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): static
    {
        $this->order = $order;
        return $this;
    }
}