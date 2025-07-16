<?php

namespace App\Entity;

use App\Repository\InstitutionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InstitutionRepository::class)]
#[ORM\Index(name: 'idx_nom', columns: ['nom'])]
#[ORM\Index(name: 'idx_adresse', columns: ['adresse'])]
#[ORM\Index(name: 'idx_telephone', columns: ['telephone'])]
#[ORM\Index(name: 'idx_courriel', columns: ['courriel'])]


class Institution
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(length: 255)] // Must be explicitly integer
    private ?int $id;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255)]
    private ?string $courriel = null;

    /**
     * @var Collection<int, Utilisateur>
     */
    #[ORM\OneToMany(targetEntity: Utilisateur::class, mappedBy: 'institution')]
    private Collection $utilisateurs;







#[ORM\Column(name: 'cree_par_id', type: 'integer', nullable: true)]  // nullable: true est la clé
private ?int $createurId = null;  // Type nullable avec valeur par déf

    public function setCreateurId(int $id): void {
        $this->createurId = $id;
    }

    public function getCreateurId(): int {
        return $this->createurId;
    }






    public function __construct()
    {
        $this->utilisateurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

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

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Collection<int, Utilisateur>
     */
    public function getUtilisateurs(): Collection
    {
        return $this->utilisateurs;
    }

    public function addUtilisateur(Utilisateur $utilisateur): static
    {
        if (!$this->utilisateurs->contains($utilisateur)) {
            $this->utilisateurs->add($utilisateur);
            $utilisateur->setInstitution($this);
        }

        return $this;
    }

    public function removeUtilisateur(Utilisateur $utilisateur): static
    {
        if ($this->utilisateurs->removeElement($utilisateur)) {
            // set the owning side to null (unless already changed)
            if ($utilisateur->getInstitution() === $this) {
                $utilisateur->setInstitution(null);
            }
        }

        return $this;
    }

}
