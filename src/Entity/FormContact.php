<?php

namespace App\Entity;

use App\Repository\FormContactRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormContactRepository::class)]
class FormContact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\Column(length: 25)]
    private ?string $Prenom = null;

    #[ORM\Column(length: 25)]
    private ?string $Nom = null;

    #[ORM\Column(length: 50)]
    private ?string $Email = null;

    #[ORM\Column(length: 50)]
    private ?string $NomInstitution = null;

    #[ORM\Column(length: 50)]
    private ?string $NomSession = null;

    #[ORM\Column(length: 50)]
    private ?string $NomModule = null;

    #[ORM\Column(length: 25)]
    private ?string $Dates = null;

    #[ORM\Column(length: 25)]
    private ?string $offre = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $message = null;

    public function getOffre(): ?string
    {
        return $this->offre;
    }

    public function setOffre(string $offre): self
    {
        $this->offre = $offre;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrenom(): ?string
    {
        return $this->Prenom;
    }

    public function setPrenom(string $Prenom): static
    {
        $this->Prenom = $Prenom;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->Nom;
    }

    public function setNom(string $Nom): static
    {
        $this->Nom = $Nom;

        return $this;
    }

public function getEmail(): ?string
{
    return $this->Email;
}

public function setEmail(string $Email): static
{
    $this->Email = $Email;
    return $this;
}

    public function getNomInstitution(): ?string
    {
        return $this->NomInstitution;
    }

    public function setNomInstitution(string $NomInstitution): static
    {
        $this->NomInstitution = $NomInstitution;

        return $this;
    }

    public function getNomSession(): ?string
    {
        return $this->NomSession;
    }

    public function setNomSession(string $NomSession): static
    {
        $this->NomSession = $NomSession;

        return $this;
    }

    public function getNomModule(): ?string
    {
        return $this->NomModule;
    }

    public function setNomModule(string $NomModule): static
    {
        $this->NomModule = $NomModule;

        return $this;
    }

    public function getDates(): ?string
    {
        return $this->Dates;
    }

    public function setDates(string $Dates): static
    {
        $this->Dates = $Dates;

        return $this;
    }


        public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;
        return $this;
    }
    
}
