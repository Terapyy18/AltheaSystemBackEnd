<?php

namespace App\Entity;

use App\Repository\SiteSettingsRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(security: 'is_granted("PUBLIC_ACCESS")'),
        new Get(security: 'is_granted("PUBLIC_ACCESS")'),
        new Patch(security: 'is_granted("ROLE_ADMIN")'),
    ],
    normalizationContext: ['groups' => ['site_settings:read']],
    denormalizationContext: ['groups' => ['site_settings:write']],
)]
#[ORM\Entity(repositoryClass: SiteSettingsRepository::class)]
class SiteSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['site_settings:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Groups(['site_settings:read'])]
    private string $settingKey;

    #[ORM\Column(type: 'text')]
    #[Groups(['site_settings:read', 'site_settings:write'])]
    private string $valueFr;

    #[ORM\Column(type: 'text')]
    #[Groups(['site_settings:read', 'site_settings:write'])]
    private string $valueEn;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['site_settings:read', 'site_settings:write'])]
    private ?string $valueHe = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['site_settings:read', 'site_settings:write'])]
    private ?string $valueZh = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['site_settings:read'])]
    private ?string $description = null;

    public function getId(): ?int { return $this->id; }

    public function getSettingKey(): string { return $this->settingKey; }
    public function setSettingKey(string $settingKey): static { $this->settingKey = $settingKey; return $this; }

    public function getValueFr(): string { return $this->valueFr; }
    public function setValueFr(string $valueFr): static { $this->valueFr = $valueFr; return $this; }

    public function getValueEn(): string { return $this->valueEn; }
    public function setValueEn(string $valueEn): static { $this->valueEn = $valueEn; return $this; }

    public function getValueHe(): ?string { return $this->valueHe; }
    public function setValueHe(?string $valueHe): static { $this->valueHe = $valueHe; return $this; }

    public function getValueZh(): ?string { return $this->valueZh; }
    public function setValueZh(?string $valueZh): static { $this->valueZh = $valueZh; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function __toString(): string { return $this->settingKey; }
}
