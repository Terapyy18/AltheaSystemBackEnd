<?php

namespace App\Entity;

use App\Repository\SupportRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(
            security: 'is_granted("ROLE_USER")',
            normalizationContext: ['groups' => ['support:read']],
        ),
        new Get(
            security: 'is_granted("ROLE_ADMIN") or object.getUser() == user',
            normalizationContext: ['groups' => ['support:read']],
        ),
        // Création de ticket : tout utilisateur authentifié
        new Post(
            security: 'is_granted("ROLE_USER")',
            denormalizationContext: ['groups' => ['support:write']],
            normalizationContext: ['groups' => ['support:read']],
        ),
        // Réponse admin (reply) : admin uniquement
        new Patch(
            security: 'is_granted("ROLE_ADMIN")',
            denormalizationContext: ['groups' => ['support:admin-write']],
            normalizationContext: ['groups' => ['support:read']],
        ),
        new Delete(
            security: 'is_granted("ROLE_ADMIN")',
        ),
    ],
)]
#[ORM\Entity(repositoryClass: SupportRepository::class)]
class Support
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['support:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['support:read', 'support:write'])]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Groups(['support:read', 'support:admin-write'])]
    private ?string $status = null;

    #[ORM\Column(length: 255)]
    #[Groups(['support:read', 'support:write'])]
    private ?string $message = null;

    #[ORM\Column(length: 255)]
    #[Groups(['support:read', 'support:write'])]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['support:read', 'support:admin-write'])]
    private ?string $reply = null;

    #[ORM\ManyToOne(inversedBy: 'support')]
    #[Groups(['support:read', 'support:write'])]
    private ?User $user = null;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getReply(): ?string
    {
        return $this->reply;
    }

    public function setReply(?string $reply): static
    {
        $this->reply = $reply;

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

    public function __toString(): string
    {
        return sprintf('Ticket #%d: %s', $this->id, $this->title) ?? 'Nouveau ticket';
    }
}
