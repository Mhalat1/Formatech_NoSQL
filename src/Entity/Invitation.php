<?php

namespace App\Entity;

use App\Repository\InvitationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InvitationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Invitation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\ManyToOne(targetEntity: Institution::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution = null;

    #[ORM\Column(length: 32, unique: true)]
    private ?string $token = null;

#[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: false)]
#[Assert\NotNull]
#[Assert\GreaterThan(propertyPath: "creeLe")]
private ?\DateTimeImmutable $expireLe;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $creeLe;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'invitationsSent')]
    #[ORM\JoinColumn(
        name: 'invited_by_id',
        referencedColumnName: 'id',
        nullable: false,
        onDelete: 'CASCADE'
    )]
    private ?Utilisateur $invitedBy = null;

    public function __construct()
    {
        $this->creeLe = new \DateTimeImmutable();
        $this->expireLe = (new \DateTimeImmutable())->add(new \DateInterval('P7D')); // Expire dans 7 jours
        $this->token = bin2hex(random_bytes(32)); 
    }

    #[ORM\PrePersist]
    public function setTimestamps(): void{
    $this->creeLe ??= new \DateTimeImmutable();
    $this->expireLe ??= (new \DateTimeImmutable())->add(new \DateInterval('P7D'));
    $this->token ??= bin2hex(random_bytes(32));
}

    // Getters et Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    public function setInstitution(?Institution $institution): static
    {
        $this->institution = $institution;
        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;
        return $this;
    }

    public function getExpirele(): ?\DateTimeImmutable
    {
        return $this->expireLe;
    }

    public function setExpirele(\DateTimeImmutable $expireLe): static
    {
        $this->expireLe = $expireLe;
        return $this;
    }

    public function getCreeLe(): ?\DateTimeImmutable
    {
        return $this->creeLe;
    }

    public function setCreeLe(\DateTimeImmutable $creeLe): static
    {
        $this->creeLe = $creeLe;
        return $this;
    }

    public function getInvitedBy(): ?Utilisateur
    {
        return $this->invitedBy;
    }

    public function setInvitedBy(?Utilisateur $invitedBy): static
    {
        $this->invitedBy = $invitedBy;
        return $this;
    }

    // Méthode utilitaire
    public function isExpired(): bool
    {
        return $this->expireLe < new \DateTimeImmutable();
    }
}