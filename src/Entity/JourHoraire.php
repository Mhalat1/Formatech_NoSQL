<?php

namespace App\Entity;

use App\Repository\JourHoraireRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JourHoraireRepository::class)]
class JourHoraire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $jour = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $datePrecise = null;

    #[ORM\Column(type: 'time')]
    private ?\DateTimeInterface $heureDebut = null;

    #[ORM\Column(type: 'time')]
    private ?\DateTimeInterface $heureFin = null;


    #[ORM\ManyToOne(targetEntity: UtilisateurInstitutionSessionModule::class, inversedBy: "horaires")]
    #[ORM\JoinColumn(name: "institution_session_module_id", referencedColumnName: "id", nullable: false)]
    private ?UtilisateurInstitutionSessionModule $utilisateurinstitutionsessionmodule = null;

    // Update getter/setter:
    public function getUtilisateurInstitutionSessionModule(): ?UtilisateurInstitutionSessionModule
    {
        return $this->utilisateurinstitutionsessionmodule;
    }

    public function setUtilisateurInstitutionSessionModule(?UtilisateurInstitutionSessionModule $uisModule): self
    {
        $this->utilisateurinstitutionsessionmodule = $uisModule;
        return $this;
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJour(): ?string
    {
        return $this->jour;
    }

    public function setJour(?string $jour): self
    {
        $this->jour = $jour;
        return $this;
    }

    public function getHeureDebut(): ?\DateTimeInterface
    {
        return $this->heureDebut;
    }

    public function setHeureDebut(\DateTimeInterface $heureDebut): self
    {
        $this->heureDebut = $heureDebut;
        return $this;
    }

    public function getHeureFin(): ?\DateTimeInterface
    {
        return $this->heureFin;
    }

    public function setHeureFin(\DateTimeInterface $heureFin): self
    {
        $this->heureFin = $heureFin;
        return $this;
    }



    public function getDatePrecise(): ?\DateTimeInterface
    {
        return $this->datePrecise;
    }

    public function setDatePrecise(?\DateTimeInterface $datePrecise): self
    {
        $this->datePrecise = $datePrecise;
        return $this;
    }
}