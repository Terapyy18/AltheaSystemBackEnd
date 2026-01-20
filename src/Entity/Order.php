<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource]
#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column]
    private ?int $shipping_number = null;

    #[ORM\Column]
    private ?\DateTime $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $payed_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $shipped_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $received_at = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $invoice_path = null;

    #[ORM\Column]
    private ?float $total_price = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?User $iduser = null;

    /**
     * @var Collection<int, ItemsOrder>
     */
    #[ORM\OneToMany(targetEntity: ItemsOrder::class, mappedBy: 'idorder')]
    private Collection $itemsOrder;

    #[ORM\ManyToOne(inversedBy: 'idorder')]
    private ?Addresses $addresses = null;

    public function __construct()
    {
        $this->itemsOrder = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getShippingNumber(): ?int
    {
        return $this->shipping_number;
    }

    public function setShippingNumber(int $shipping_number): static
    {
        $this->shipping_number = $shipping_number;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTime $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getPayedAt(): ?\DateTime
    {
        return $this->payed_at;
    }

    public function setPayedAt(?\DateTime $payed_at): static
    {
        $this->payed_at = $payed_at;

        return $this;
    }

    public function getShippedAt(): ?\DateTime
    {
        return $this->shipped_at;
    }

    public function setShippedAt(?\DateTime $shipped_at): static
    {
        $this->shipped_at = $shipped_at;

        return $this;
    }

    public function getReceivedAt(): ?\DateTime
    {
        return $this->received_at;
    }

    public function setReceivedAt(?\DateTime $received_at): static
    {
        $this->received_at = $received_at;

        return $this;
    }

    public function getInvoicePath(): ?string
    {
        return $this->invoice_path;
    }

    public function setInvoicePath(?string $invoice_path): static
    {
        $this->invoice_path = $invoice_path;

        return $this;
    }

    public function getTotalPrice(): ?float
    {
        return $this->total_price;
    }

    public function setTotalPrice(float $total_price): static
    {
        $this->total_price = $total_price;

        return $this;
    }

    public function getIdUser(): ?User
    {
        return $this->iduser;
    }

    public function setIdUser(?User $iduser): static
    {
        $this->iduser = $iduser;

        return $this;
    }

    /**
     * @return Collection<int, ItemsOrder>
     */
    public function getItemsOrder(): Collection
    {
        return $this->itemsOrder;
    }

    public function addItemsOrder(ItemsOrder $itemsOrder): static
    {
        if (!$this->itemsOrder->contains($itemsOrder)) {
            $this->itemsOrder->add($itemsOrder);
            $itemsOrder->setIdOrder($this);
        }

        return $this;
    }

    public function removeItemsOrder(ItemsOrder $itemsOrder): static
    {
        if ($this->itemsOrder->removeElement($itemsOrder)) {
            // set the owning side to null (unless already changed)
            if ($itemsOrder->getIdOrder() === $this) {
                $itemsOrder->setIdOrder(null);
            }
        }

        return $this;
    }

    public function getAddresses(): ?Addresses
    {
        return $this->addresses;
    }

    public function setAddresses(?Addresses $addresses): static
    {
        $this->addresses = $addresses;

        return $this;
    }
}
