<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\EntityRepository;

use App\Repository\SessionModuleRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: SessionModuleRepository::class)]
#[ORM\Index(name: 'idx_session_id', columns: ['session_id'])]
#[ORM\Index(name: 'idx_module_id', columns: ['module_id'])]
#[ORM\Index(name: 'idx_institution_id', columns: ['institution_id'])]

class SessionModule
{

// SUPPRESION EN CASCADE DES ASSOCIATIONS //// ==================================================
// GESTION DE LA SUPPRESSION EN CASCADE DES ASSOCIATIONS
// ==================================================

/**
 * Relation OneToMany vers UtilisateurInstitutionSessionModule.
 * 
 * - cascade: ['persist', 'remove'] signifie que:
 *   - Quand ce SessionModule est persisté, tous ses UtilisateurInstitutionSessionModule liés le seront aussi
 *   - Quand ce SessionModule est supprimé, tous ses UtilisateurInstitutionSessionModule liés seront automatiquement supprimés
 * 
 * mappedBy: 'sessionModule' indique que la relation est gérée côté UtilisateurInstitutionSessionModule
 */
#[ORM\OneToMany(
    targetEntity: UtilisateurInstitutionSessionModule::class, 
    mappedBy: 'sessionModule', 
    cascade: ['persist', 'remove']
)]
private Collection|PersistentCollection $userAssociations;

/**
 * Initialise la collection d'associations utilisateurs.
 * Cette méthode est appelée automatiquement lors de la création de l'entité.
 */
public function constructUtilisateurInstitutionSessionModule()
{
    $this->userAssociations = new ArrayCollection();
}

// ==================================================
// RELATION AVEC MODULE (avec suppression en cascade au niveau SQL)
// ==================================================

/**
 * Relation ManyToOne vers Module.
 * 
 * - inversedBy: "sessionModules" fait le lien avec la propriété correspondante dans Module
 * 
 * L'annotation JoinColumn avec onDelete: "CASCADE" signifie que:
 * - Si le Module référencé est supprimé en base de données (via requête SQL directe)
 * - TOUS les SessionModule liés seront automatiquement supprimés par le SGBD
 * - C'est une sécurité au niveau base de données, complémentaire au comportement Doctrine
 */
// Déclaration d'une relation ManyToOne (plusieurs SessionModule peuvent être liés à un seul Module)
#[ORM\ManyToOne(
    // Spécifie l'entité cible de la relation (ici la classe Module)
    targetEntity: Module::class,
    // Nom de la propriété dans l'entité Module qui fait référence à cette relation
    inversedBy: "sessionModules"
)]
// Configuration de la colonne de jointure en base de données
#[ORM\JoinColumn(
    // Nom de la colonne dans la table session_module qui stocke la clé étrangère
    name: "module_id",
    // Nom de la colonne référencée dans la table module (généralement l'id)
    referencedColumnName: "id",
    // Comportement lors de la suppression du Module référencé :
    // CASCADE = suppression automatique des SessionModule liés quand on supprime leur Module
    onDelete: "CASCADE"  // Instruction exécutée au niveau SQL par le SGBD
)]
// Déclaration de la propriété avec typage PHP (peut être null ou un objet Module)
private ?Module $module = null;  // Variable qui contiendra l'objet Module associé



    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')] // Must be explicitly integer
    private ?int $id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn]
    private ?Session $session = null;



    #[ORM\ManyToOne(fetch: "EAGER")]
    #[ORM\JoinColumn(columnDefinition: "INT NOT NULL")]
    private ?Institution $institution = null;  // Nouvelle propriété pour la relation avec Institution



    #[ORM\OneToMany(targetEntity: JourHoraire::class, mappedBy: "sessionModule")]
    private Collection $horaires;

    public function __construct()
    {
        $this->horaires = new ArrayCollection();
    }

    public function getHoraires(): Collection
    {
        return $this->horaires;
    }




    public function getId(): int
    {
        return $this->id;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session): static
    {
        $this->session = $session;

        return $this;
    }

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function setModule(?Module $module): static
    {
        $this->module = $module;

        return $this;
    }

    // Getter et Setter pour la propriété institution
    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    public function setInstitution(?Institution $institution): static
    {
        $this->institution = $institution;

        return $this;
    }
}
