<?php
// src/Entity/Utilisateur.php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\Index(name: 'idx_prenom', columns: ['prenom'])]
#[ORM\Index(name: 'idx_nom', columns: ['nom'])]
#[ORM\Index(name: 'idx_courriel', columns: ['courriel'])]
#[ORM\Index(name: 'idx_commentaire', columns: ['commentaire'])]
#[ORM\Index(name: 'idx_note', columns: ['note'])]
#[ORM\Index(name: 'idx_telephone', columns: ['telephone'])]
#[ORM\Index(name: 'idx_date_naissance', columns: ['date_naissance'])]
#[UniqueEntity(fields: ['courriel'], message: 'un compte avec cet email existe déja')]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $courriel = null;

    #[ORM\Column(length: 255)]
    private ?string $motdepasse = null;

    #[ORM\Column(type: 'json')]
    private array $roles = ['ROLE_USER'];

    #[ORM\Column(length: 255)]
    private ?string $commentaire = "ajouter commentaire";

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?string $note = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateNaissance = null;

    #[ORM\Column(type: 'integer')]
    private int $nombreSouscriptions = 0;

    #[ORM\Column(type: 'datetime', nullable: true, name: 'date_fin_abonnement')]
    private ?\DateTimeInterface $dateFinAbonnement = null;

    #[ORM\ManyToOne(inversedBy: 'utilisateurs')]
    private ?Institution $institution = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $nombreInvitations = 0;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $dateCreation = null;





    #[ORM\Column(name: 'nombre_institutions', type: 'integer')]
    private int $nombreInstitutions = 0;


    public function incrementCompteur(): self  // Retourne $this pour le chaînage
    {
        $this->nombreInstitutions++;
        return $this;
    }
    public function getNombreInstitutions(): int {
        return $this->nombreInstitutions;
    }





    #[ORM\Column(length: 255, nullable: true)]
    private ?string $abonnementType = null; // 'monthly' ou 'yearly'
        
    public function getabonnementType(): ?string
    {
        return $this->abonnementType;
    }

    public function setabonnementType(?string $type): self
    {
        $this->abonnementType = $type;
        return $this;
    }
    


    public function getInvitations(): int
    {
        return $this->nombreInvitations;
    }

    // Setter (utilisé par le listener)
    public function setInvitations(int $nombreInvitations): self
    {
        $this->nombreInvitations = $nombreInvitations;
        return $this;
    }

public function compteurInvitations(): int
{
    return $this->nombreInvitations;
}

    public function incrementNombreInvitations(): self  
    {
        $this->nombreInvitations++;
        return $this;
    }

    public function incrementCompteurAbonnement(): self 
    {
        $this->nombreSouscriptions++;
        return $this;
    }

    public function getNombreSouscriptions(): int
    {
        return $this->nombreSouscriptions;
    }

    public function setNombreSouscriptions(int $count): self
    {
        $this->nombreSouscriptions = $count;
        return $this;
    }

    public function getDateFinAbonnement(): ?\DateTimeInterface
    {
        return $this->dateFinAbonnement;
    }

    public function setDateFinAbonnement(?\DateTimeInterface $dateFinAbonnement): self
    {
        $this->dateFinAbonnement = $dateFinAbonnement;
        return $this;
    }

    public function verifierSiAbonnementActif(): bool
    {
        if ($this->dateFinAbonnement === null) {
            return false;
        }
        
        $now = new \DateTime('now', $this->dateFinAbonnement->getTimezone());
        return $this->dateFinAbonnement > $now;
    }

    // Méthodes UserInterface
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->motdepasse;
    }

    public function setPassword(string $motdepasse): static
    {
        $this->motdepasse = $motdepasse;
        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->courriel;
    }

    public function eraseCredentials(): void
    {
        // Nothing to do here
    }

    // Getters and setters standards
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getCourriel(): ?string
    {
        return $this->courriel;
    }

    public function setCourriel(string $courriel): static
    {
        $this->courriel = $courriel;
        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): static
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(?\DateTimeInterface $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;
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


    public function getDateCreation(): ?\DateTimeImmutable
{
    return $this->dateCreation;
}

public function setDateCreation(\DateTimeImmutable $dateCreation): self
{
    $this->dateCreation = $dateCreation;
    return $this;
}

}