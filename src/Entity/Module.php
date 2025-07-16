<?php

namespace App\Entity;

use App\Repository\ModuleRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: ModuleRepository::class)]
#[ORM\Index(name: 'idx_nom', columns: ['nom'])]
#[ORM\Index(name: 'idx_description', columns: ['description'])]
#[ORM\Index(name: 'idx_commentaire', columns: ['commentaire'])]
#[ORM\Index(name: 'idx_date_debut_module', columns: ['date_debut'])]
#[ORM\Index(name: 'idx_date_fin_module', columns: ['date_fin'])]
class Module
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]  // Removed length:255 for integer ID
    private ?int $id = null;  // Initialize as null

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $commentaire = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_debut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_fin = null;

    #[ORM\OneToMany(
        targetEntity: SessionModule::class, 
        mappedBy: "module",
        cascade: ["persist", "remove"],
        orphanRemoval: true
    )]
    private Collection $sessionModules;

    #[ORM\OneToMany(
        targetEntity: JourHoraire::class, 
        mappedBy: "module",
        cascade: ["persist"],
        orphanRemoval: false
    )]
    // private Collection $horaires;

    // public function __construct()
    // {
    //     $this->sessionModules = new ArrayCollection();
    //     $this->horaires = new ArrayCollection();
    // }


    public function getId(): ?int
    {
        return $this->id;
    }

    
    public function setId(int $id): static
    {
        $this->id = $id;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

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



        // Updated getter and setter for date_debut
    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->date_debut;
    }

    public function setDateDebut(\DateTimeInterface $date_debut): static
    {
        $this->date_debut = $date_debut;

        return $this;
    }

    // Updated getter and setter for date_fin
    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function setDateFin(\DateTimeInterface $date_fin): static
    {
        $this->date_fin = $date_fin;

        return $this;
    }
    // public function getHoraires(): Collection
    // {
    //     return $this->horaires;
    // }
    


    
    public function getSessionModules(): Collection
    {
        return $this->sessionModules;
    }

    public function addSessionModule(SessionModule $sessionModule): static
    {
        if (!$this->sessionModules->contains($sessionModule)) {
            $this->sessionModules->add($sessionModule);
            $sessionModule->setModule($this);
        }
        return $this;
    }

    public function removeSessionModule(SessionModule $sessionModule): static
    {
        if ($this->sessionModules->removeElement($sessionModule)) {
            // set the owning side to null (unless already changed)
            if ($sessionModule->getModule() === $this) {
                $sessionModule->setModule(null);
            }
        }
        return $this;
    }


}
