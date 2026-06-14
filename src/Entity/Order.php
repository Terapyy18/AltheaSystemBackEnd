<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
#[ApiResource(
    operations: [
        new GetCollection(
            security: 'is_granted("ROLE_USER")',
            normalizationContext: ['groups' => ['order:read']],
        ),
        new Get(
            security: 'is_granted("ROLE_ADMIN") or object.getUser() == user',
            normalizationContext: ['groups' => ['order:read']],
        ),
        new Post(
            security: 'is_granted("ROLE_USER")',
            denormalizationContext: ['groups' => ['order:write']],
            normalizationContext: ['groups' => ['order:read']],
        ),
        new Put(
            security: 'is_granted("ROLE_ADMIN")',
            denormalizationContext: ['groups' => ['order:write']],
            normalizationContext: ['groups' => ['order:read']],
        ),
        new Delete(
            security: 'is_granted("ROLE_ADMIN")',
        ),
    ],
)]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['order:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['order:read', 'order:write'])]
    private ?string $status = null;

    #[ORM\Column(name: 'shipping_number', type: Types::INTEGER, nullable: true)]
    #[Groups(['order:read', 'order:write'])]
    private ?int $shippingNumber = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    #[Groups(['order:read', 'order:write'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'payed_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['order:read', 'order:write'])]
    private ?\DateTimeInterface $payedAt = null;

    #[ORM\Column(name: 'shipped_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['order:read', 'order:write'])]
    private ?\DateTimeInterface $shippedAt = null;

    #[ORM\Column(name: 'received_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['order:read', 'order:write'])]
    private ?\DateTimeInterface $receivedAt = null;

    #[ORM\Column(name: 'invoice_path', length: 255, nullable: true)]
    #[Groups(['order:read', 'order:write'])]
    private ?string $invoicePath = null;

    #[ORM\Column(name: 'invoice_number', length: 50, nullable: true)]
    #[Groups(['order:read'])]
    private ?string $invoiceNumber = null;

    #[ORM\Column(name: 'total_price', type: Types::FLOAT)]
    #[Groups(['order:read', 'order:write'])]
    private ?float $totalPrice = null;

    #[ORM\Column(name: 'stripe_session_id', length: 255, nullable: true, unique: true)]
    #[Groups(['order:read'])]
    private ?string $stripeSessionId = null;

    #[ORM\Column(name: 'stock_decremented', type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups(['order:read'])]
    private bool $stockDecremented = false;

    #[ORM\Column(name: 'stripe_payment_intent_id', length: 255, nullable: true, unique: true)]
    #[Groups(['order:read'])]
    private ?string $stripePaymentIntentId = null;

    /**
     * Email de l'acheteur lorsque la commande est passée en mode invité
     * (user === null). Pour une commande d'un compte connecté, ce champ
     * reste null et l'email est porté par la relation User.
     */
    #[ORM\Column(name: 'guest_email', length: 255, nullable: true)]
    #[Groups(['order:read'])]
    private ?string $guestEmail = null;

    /**
     * Raison sociale de l'acheteur en mode invité (plateforme B2B : le client
     * d'une commande invité est une entreprise). Null pour un compte connecté.
     */
    #[ORM\Column(name: 'guest_company', length: 255, nullable: true)]
    #[Groups(['order:read'])]
    private ?string $guestCompany = null;

    /**
     * Numéro SIREN (9 chiffres) ou SIRET (14) de l'entreprise invitée, requis
     * pour la facturation B2B. Null pour un compte connecté (le SIREN est alors
     * porté par la relation User).
     */
    #[ORM\Column(name: 'guest_siren', length: 14, nullable: true)]
    #[Groups(['order:read'])]
    private ?string $guestSiren = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true)]
    #[Groups(['order:read', 'order:write'])]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Addresses::class)]
    #[ORM\JoinColumn(name: 'addresses_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['order:read', 'order:write'])]
    private ?Addresses $addresses = null;

    /**
     * Relation inverse : n'existe pas en colonne dans la table 'order'
     * @var Collection<int, ItemsOrder>
     */
    #[ORM\OneToMany(targetEntity: ItemsOrder::class, mappedBy: 'order')]
    #[Groups(['order:read'])] // On ne l'affiche qu'en lecture (GET)
    private Collection $itemsOrders;

    public function __construct()
    {
        $this->itemsOrders = new ArrayCollection();
        $this->createdAt = new \DateTime();
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
        return $this->shippingNumber;
    }

    public function setShippingNumber(?int $shippingNumber): static
    {
        $this->shippingNumber = $shippingNumber;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getPayedAt(): ?\DateTimeInterface
    {
        return $this->payedAt;
    }

    public function setPayedAt(?\DateTimeInterface $payedAt): static
    {
        $this->payedAt = $payedAt;
        return $this;
    }

    public function getShippedAt(): ?\DateTimeInterface
    {
        return $this->shippedAt;
    }

    public function setShippedAt(?\DateTimeInterface $shippedAt): static
    {
        $this->shippedAt = $shippedAt;
        return $this;
    }

    public function getReceivedAt(): ?\DateTimeInterface
    {
        return $this->receivedAt;
    }

    public function setReceivedAt(?\DateTimeInterface $receivedAt): static
    {
        $this->receivedAt = $receivedAt;
        return $this;
    }

    public function getInvoicePath(): ?string
    {
        return $this->invoicePath;
    }

    public function setInvoicePath(?string $invoicePath): static
    {
        $this->invoicePath = $invoicePath;
        return $this;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(?string $invoiceNumber): static
    {
        $this->invoiceNumber = $invoiceNumber;
        return $this;
    }

    public function getTotalPrice(): ?float
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(float $totalPrice): static
    {
        $this->totalPrice = $totalPrice;
        return $this;
    }

    public function getStripeSessionId(): ?string
    {
        return $this->stripeSessionId;
    }

    public function setStripeSessionId(?string $stripeSessionId): static
    {
        $this->stripeSessionId = $stripeSessionId;
        return $this;
    }

    public function isStockDecremented(): bool
    {
        return $this->stockDecremented;
    }

    public function setStockDecremented(bool $stockDecremented): static
    {
        $this->stockDecremented = $stockDecremented;
        return $this;
    }

    public function getStripePaymentIntentId(): ?string
    {
        return $this->stripePaymentIntentId;
    }

    public function setStripePaymentIntentId(?string $stripePaymentIntentId): static
    {
        $this->stripePaymentIntentId = $stripePaymentIntentId;
        return $this;
    }

    public function getGuestEmail(): ?string
    {
        return $this->guestEmail;
    }

    public function setGuestEmail(?string $guestEmail): static
    {
        $this->guestEmail = $guestEmail;
        return $this;
    }

    public function getGuestCompany(): ?string
    {
        return $this->guestCompany;
    }

    public function setGuestCompany(?string $guestCompany): static
    {
        $this->guestCompany = $guestCompany;
        return $this;
    }

    public function getGuestSiren(): ?string
    {
        return $this->guestSiren;
    }

    public function setGuestSiren(?string $guestSiren): static
    {
        $this->guestSiren = $guestSiren;
        return $this;
    }

    /**
     * Email de contact de la commande, qu'elle soit passée par un compte
     * connecté ou en mode invité.
     */
    public function getContactEmail(): ?string
    {
        return $this->user?->getEmail() ?? $this->guestEmail;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
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
            $itemsOrder->setOrder($this);
        }
        return $this;
    }

    public function removeItemsOrder(ItemsOrder $itemsOrder): static
    {
        if ($this->itemsOrders->removeElement($itemsOrder)) {
            if ($itemsOrder->getOrder() === $this) {
                $itemsOrder->setOrder(null);
            }
        }
        return $this;
    }

    public function __toString(): string {
        return 'Commande #' . $this->id; 
    }
}