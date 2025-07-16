<?php

namespace App\Entity;

use App\Repository\UtilisateurInstitutionSessionModuleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UtilisateurInstitutionSessionModuleRepository::class)]
#[ORM\Index(name: 'idx_commentaire_module', columns: ['commentaire_module'])]
#[ORM\Index(name: 'idx_note_module', columns: ['note_module'])]
#[ORM\Index(name: 'idx_utilisateur_id', columns: ['utilisateur_id'])]
#[ORM\Index(name: 'idx_session_module_id', columns: ['session_module_id'])]




class UtilisateurInstitutionSessionModule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(targetEntity: SessionModule::class)]
    #[ORM\JoinColumn(name: 'session_module_id', referencedColumnName: 'id')]
    private ?SessionModule $sessionModule = null;
    
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commentaire_module = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $note_module = null;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSessionModule(): ?SessionModule
    {
        return $this->sessionModule;
    }

    public function setSessionModule(?SessionModule $sessionModule): static
    {
        $this->sessionModule = $sessionModule;
        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }
    
    public function getCommentaireModule(): ?string
    {
        return $this->commentaire_module;
    }

    public function setCommentaireModule(string $commentaire_module): self
    {
        $this->commentaire_module = $commentaire_module;
        return $this;
    }

    public function getNoteModule(): ?float
    {
        return $this->note_module;
    }

    public function setNoteModule(?float $note_module): self
    {
        $this->note_module = $note_module;
        return $this;
    }

    public function getUtilisateurId(): ?int
    {
        return $this->utilisateur?->getId();
    }
    
    public function getSessionModuleId(): ?int
    {
        return $this->sessionModule?->getId();
    }
}