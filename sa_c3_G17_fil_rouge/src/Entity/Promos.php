<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\PromosRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     collectionOperations={
 *          "get_promos" = {
 *              "method"="GET",
 *              "path"="/admins/promos",
 *              "normalization_context"={"groups"={"promos:read"}},
 *              "security"="is_granted('ROLE_FORMATEUR')",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *           },
 *          "get_principal" = {
 *              "method"="GET",
 *              "path"="/admins/promos/principal",
 *              "normalization_context"={"groups"={"promos:read","promos:appreant:read"}},
 *              "security"="is_granted('ROLE_FORMATEUR')",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *           },
 *          "get_attente" = {
 *              "method"="GET",
 *              "path"="/admins/promos/apprenants/attente",
 *              "normalization_context"={"groups"={"promos:read"}},
 *              "security"="is_granted('ROLE_FORMATEUR')",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *           },
 *          "add_promo" = {
 *              "method"="POST",
 *              "path"="/admins/promos",
 *              "normalization_context"={"groups"={"promos:read"}},
 *              "security"="is_granted('ROLE_FORMATEUR')",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *           }
 *     },
 *     itemOperations={
 *
 *          "get_promo" = {
 *              "method"="GET",
 *              "path"="/admins/promos/{id}",
 *              "normalization_context"={"groups"={"promos:read"}},
 *              "security"="is_granted('VIEW',object)",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *           },
 *          "get_principal" = {
 *              "method"="GET",
 *              "path"="/admins/promos/{id}/principal",
 *              "normalization_context"={"groups"={"promos:read"}},
 *              "security"="is_granted('VIEW',object)",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *           },
 *          "set_promo" = {
 *              "method" = "PUT",
 *              "path" = "admin/promos/{id}",
 *              "requirements"={"id"="\d+"},
 *              "normalization_context"={"groups"={"promos:read"}},
 *              "security"="is_granted('SET',object)",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *          },
 *          "setFormateur" = {
 *              "method" = "PUT",
 *              "path" = "/admins/promos/{id}/formateurs",
 *              "normalization_context"={"groups"={"promos:read"}},
 *              "security"="is_granted('SET',object)",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *          },
 *          "setStatutGroupe" = {
 *              "method" = "PUT",
 *              "path" = "/admins/promos/{idPromo}/groupes/{idGroupe}",
 *              "normalization_context"={"groups"={"promos:read"}},
 *              "security"="is_granted('SET',object)",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *          }
 *     },
 * )
 * @ORM\Entity(repositoryClass=PromosRepository::class)
 */
class Promos
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"promos:read","getBriefs:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(
     *     message="Le choix de la langue est obligatoire"
     * )
     * @Groups({"promos:read","getBriefs:read"})
     */
    private $langue;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(
     *     message="Le titre est obligatoire"
     * )
     * @Groups({"promos:read","getBriefs:read"})
     */
    private $titre;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(
     *     message="La description de la promo est obligatoire"
     * )
     * @Groups({"promos:read","getBriefs:read"})
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @Groups({"promos:read","getBriefs:read"})
     */
    private $lieu;

    /**
     * @ORM\Column(type="date")
     * @Assert\NotBlank(
     *     message="La date de début est obligatoire"
     * )
     * @Groups({"promos:read","getBriefs:read"})
     */
    private $dateDebut;

    /**
     * @ORM\Column(type="date")
     * @Assert\NotBlank(
     *     message="La date de fin est obligatoire"
     * )
     * @Groups({"promos:read","getBriefs:read"})
     */
    private $dateFinProvisoire;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"promos:read","getBriefs:read"})
     * @Assert\NotBlank(
     *     message="La fabrique est obligatoire"
     * )
     */
    private $fabrique;

    /**
     * @ORM\Column(type="date",nullable=true)
     *
     */
    private $dateFinReelle;


    /**
     * @ORM\ManyToOne(targetEntity=Referentiel::class, inversedBy="promos")
     * @Assert\NotBlank(
     *     message="Le choix du referentiel est obligatoire"
     * )
     * @Groups({"promos:read"})
     */
    private $referentiel;

    /**
     * @ORM\ManyToMany(targetEntity=Formateur::class, inversedBy="promos")
     * @Groups({"promos:read"})
     */
    private $formateur;

    /**
     * @ORM\OneToMany(targetEntity=Groupes::class, mappedBy="promos")
     * @Assert\NotBlank(
     *     message="Le choix des groupes est obligatoire"
     * )
     * @Groups({"promos:read"})
     */
    private $groupes;

    /**
     * @ORM\Column(type="boolean",nullable=true)
     */
    private $isDeleted;

    /**
     * @ORM\ManyToOne(targetEntity=Admin::class, inversedBy="promos")
     */
    private $admin;

    /**
     * @ORM\Column(type="boolean")
     */
    private $etat;

    /**
     * @ORM\OneToMany(targetEntity=PromoBrief::class, mappedBy="promo")
     */
    private $promoBriefs;

    /**
     * @ORM\OneToMany(targetEntity=StatistiquesCompetences::class, mappedBy="promo")
     */
    private $statistiquesCompetences;

    public function __construct()
    {
        $this->formateur = new ArrayCollection();
        $this->groupes = new ArrayCollection();
        $this->promoBriefs = new ArrayCollection();
        $this->statistiquesCompetences = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getLangue(): ?string
    {
        return $this->langue;
    }

    public function setLangue(string $langue): self
    {
        $this->langue = $langue;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(string $lieu): self
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFinProvisoire(): ?\DateTimeInterface
    {
        return $this->dateFinProvisoire;
    }

    public function setDateFinProvisoire(\DateTimeInterface $dateFinProvisoire): self
    {
        $this->dateFinProvisoire = $dateFinProvisoire;

        return $this;
    }

    public function getFabrique(): ?string
    {
        return $this->fabrique;
    }

    public function setFabrique(string $fabrique): self
    {
        $this->fabrique = $fabrique;

        return $this;
    }

    public function getDateFinReelle(): ?\DateTimeInterface
    {
        return $this->dateFinReelle;
    }

    public function setDateFinReelle(\DateTimeInterface $dateFinReelle): self
    {
        $this->dateFinReelle = $dateFinReelle;

        return $this;
    }

    public function getReferentiel(): ?Referentiel
    {
        return $this->referentiel;
    }

    public function setReferentiel(?Referentiel $referentiel): self
    {
        $this->referentiel = $referentiel;

        return $this;
    }

    /**
     * @return Collection|Formateur[]
     */
    public function getFormateur(): Collection
    {
        return $this->formateur;
    }

    public function addFormateur(Formateur $formateur): self
    {
        if (!$this->formateur->contains($formateur)) {
            $this->formateur[] = $formateur;
        }

        return $this;
    }

    public function removeFormateur(Formateur $formateur): self
    {
        if ($this->formateur->contains($formateur)) {
            $this->formateur->removeElement($formateur);
        }

        return $this;
    }

    /**
     * @return Collection|Groupes[]
     */
    public function getGroupes(): Collection
    {
        return $this->groupes;
    }

    public function addGroupe(Groupes $groupe): self
    {
        if (!$this->groupes->contains($groupe)) {
            $this->groupes[] = $groupe;
            $groupe->setPromos($this);
        }

        return $this;
    }

    public function removeGroupe(Groupes $groupe): self
    {
        if ($this->groupes->contains($groupe)) {
            $this->groupes->removeElement($groupe);
            // set the owning side to null (unless already changed)
            if ($groupe->getPromos() === $this) {
                $groupe->setPromos(null);
            }
        }

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getAdmin(): ?Admin
    {
        return $this->admin;
    }

    public function setAdmin(?Admin $admin): self
    {
        $this->admin = $admin;

        return $this;
    }

    public function getEtat(): ?bool
    {
        return $this->etat;
    }

    public function setEtat(bool $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * @return Collection|PromoBrief[]
     */
    public function getPromoBriefs(): Collection
    {
        return $this->promoBriefs;
    }

    public function addPromoBrief(PromoBrief $promoBrief): self
    {
        if (!$this->promoBriefs->contains($promoBrief)) {
            $this->promoBriefs[] = $promoBrief;
            $promoBrief->setPromo($this);
        }

        return $this;
    }

    public function removePromoBrief(PromoBrief $promoBrief): self
    {
        if ($this->promoBriefs->contains($promoBrief)) {
            $this->promoBriefs->removeElement($promoBrief);
            // set the owning side to null (unless already changed)
            if ($promoBrief->getPromo() === $this) {
                $promoBrief->setPromo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|StatistiquesCompetences[]
     */
    public function getStatistiquesCompetences(): Collection
    {
        return $this->statistiquesCompetences;
    }

    public function addStatistiquesCompetence(StatistiquesCompetences $statistiquesCompetence): self
    {
        if (!$this->statistiquesCompetences->contains($statistiquesCompetence)) {
            $this->statistiquesCompetences[] = $statistiquesCompetence;
            $statistiquesCompetence->setPromo($this);
        }

        return $this;
    }

    public function removeStatistiquesCompetence(StatistiquesCompetences $statistiquesCompetence): self
    {
        if ($this->statistiquesCompetences->contains($statistiquesCompetence)) {
            $this->statistiquesCompetences->removeElement($statistiquesCompetence);
            // set the owning side to null (unless already changed)
            if ($statistiquesCompetence->getPromo() === $this) {
                $statistiquesCompetence->setPromo(null);
            }
        }

        return $this;
    }
}
