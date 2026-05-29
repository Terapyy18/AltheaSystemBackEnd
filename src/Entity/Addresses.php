<?php

namespace App\Entity;

use App\Repository\AddressesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    normalizationContext: ['groups' => ['address:read']],
    denormalizationContext: ['groups' => ['address:write']],
)]
#[ORM\Entity(repositoryClass: AddressesRepository::class)]
class Addresses
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['address:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['address:read', 'address:write'])]
    private ?string $address = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['address:read', 'address:write'])]
    private ?string $city = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Groups(['address:read', 'address:write'])]
    private ?int $postal_code = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['address:read', 'address:write'])]
    private ?string $province = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(exactly: 2)]
    #[Groups(['address:read', 'address:write'])]
    private ?string $country_code = null;

    #[ORM\ManyToOne(inversedBy: 'addresses')]
    #[Groups(['address:read', 'address:write'])]
    private ?User $user = null;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'addresses')]
    // Pas de groupe : invisible en lecture ET en écriture via API
    private Collection $id_order;

    public function __construct()
    {
        $this->id_order = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;
        return $this;
    }

    #[SerializedName('postal_code')]
    public function getPostalCode(): ?int
    {
        return $this->postal_code;
    }

    #[SerializedName('postal_code')]
    public function setPostalCode(int $postal_code): static
    {
        $this->postal_code = $postal_code;
        return $this;
    }

    public function getProvince(): ?string
    {
        return $this->province;
    }

    public function setProvince(string $province): static
    {
        $this->province = $province;
        return $this;
    }

    #[SerializedName('country_code')]
    public function getCountryCode(): ?string
    {
        return $this->country_code;
    }

    #[SerializedName('country_code')]
    public function setCountryCode(string $country_code): static
    {
        $this->country_code = $country_code;
        return $this;
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

    /**
     * @return Collection<int, Order>
     */
    public function getIdOrder(): Collection
    {
        return $this->id_order;
    }

    public function addIdOrder(Order $idOrder): static
    {
        if (!$this->id_order->contains($idOrder)) {
            $this->id_order->add($idOrder);
            $idOrder->setAddresses($this);
        }
        return $this;
    }

    public function removeIdOrder(Order $idOrder): static
    {
        if ($this->id_order->removeElement($idOrder)) {
            if ($idOrder->getAddresses() === $this) {
                $idOrder->setAddresses(null);
            }
        }
        return $this;
    }

    // src/Entity/Addresses.php
    public function __toString(): string
    {
        return sprintf('%s, %s (%s)', $this->address, $this->city, $this->postal_code);
    }
}