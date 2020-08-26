<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\BriefRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource(
 *     collectionOperations={
 *          "add_brief"={
 *              "method"="POST",
 *              "path"="/formateurs/briefs",
 *              "security"="is_granted('ROLE_FORMATEUR')",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *          },
 *          "duplicate_brief"={
 *              "method"="POST",
 *              "path"="/formateurs/briefs/{id}",
 *              "security"="is_granted('ROLE_FORMATEUR')",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *
 *          },
 *          "add_url_livrables_attendus"={
 *              "method"="POST",
 *              "path"="/apprenants/{idStudent}/groupe/{id}/livrables",
 *              "security"="is_granted('ROLE_FORMATEUR')",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *
 *          }
 *     },
 *      itemOperations={
 *          "add_assignation"={
 *              "method"="PUT",
 *              "path" = "/formateurs/promo/{idPromo}/brief/{id}/assignation",
 *              "security" = "is_granted('ROLE_FORMATEUR')",
 *              "security_message" = "Vous n'avez pas access à cette Ressource",
 *          },
 *          "set_brief"={
 *              "method"="PUT",
 *              "path"="/formateurs/promo/{idPromo}/brief/{id}",
 *              "requirements"={"id"="\d+"},
 *              "security"="is_granted('ROLE_FORMATEUR')",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *          },
 *          "delete_brief"={
 *              "method"="DELETE",
 *              "path"="/formateurs/promo/{idPromo}/brief/{id}",
 *              "requirements"={"id"="\d+"},
 *              "security"="is_granted('ROLE_FORMATEUR')",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *          }
 * 
 *     },
 * )
 * @ORM\Entity(repositoryClass=BriefRepository::class)
 */
class Brief
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $langue;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $titre;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $contexte;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $livrable;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $modalitesPedagogiques;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $critereDePerformance;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $modalitesEvaluation;

    /**
     * @ORM\Column(type="blob", nullable=true)
     */
    private $avatar;

    /**
     * @ORM\Column(type="date")
     */
    private $dateCreation;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $statutBrief;

    /**
     * @ORM\ManyToMany(targetEntity=Groupes::class, inversedBy="briefs")
     */
    private $groupes;

    /**
     * @ORM\ManyToOne(targetEntity=Formateur::class, inversedBy="briefs")
     */
    private $formateur;

    /**
     * @ORM\ManyToOne(targetEntity=Referentiel::class, inversedBy="briefs")
     */
    private $referentiel;

    /**
     * @ORM\OneToMany(targetEntity=Niveau::class, mappedBy="brief")
     */
    private $niveaux;

    /**
     * @ORM\ManyToMany(targetEntity=Tag::class, inversedBy="briefs")
     */
    private $tags;

    /**
     * @ORM\OneToMany(targetEntity=Ressource::class, mappedBy="brief")
     */
    private $ressources;

    /**
     * @ORM\OneToMany(targetEntity=PromoBrief::class, mappedBy="brief",cascade={"persist"})
     */
    private $promoBriefs;

    /**
     * @ORM\ManyToMany(targetEntity=LivrableAttendu::class, mappedBy="briefs")
     */
    private $livrableAttendus;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDeleted;

    public function __construct()
    {
        $this->groupes = new ArrayCollection();
        $this->niveaux = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->ressources = new ArrayCollection();
        $this->promoBriefs = new ArrayCollection();
        $this->livrableAttendus = new ArrayCollection();
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

    public function getContexte(): ?string
    {
        return $this->contexte;
    }

    public function setContexte(string $contexte): self
    {
        $this->contexte = $contexte;

        return $this;
    }

    public function getlivrable(): ?string
    {
        return $this->livrable;
    }

    public function setlivrable(string $livrable): self
    {
        $this->livrable = $livrable;

        return $this;
    }

    public function getModalitesPedagogiques(): ?string
    {
        return $this->modalitesPedagogiques;
    }

    public function setModalitesPedagogiques(string $modalitesPedagogiques): self
    {
        $this->modalitesPedagogiques = $modalitesPedagogiques;

        return $this;
    }

    public function getCritereDePerformance(): ?string
    {
        return $this->critereDePerformance;
    }

    public function setCritereDePerformance(string $critereDePerformance): self
    {
        $this->critereDePerformance = $critereDePerformance;

        return $this;
    }

    public function getModalitesEvaluation(): ?string
    {
        return $this->modalitesEvaluation;
    }

    public function setModalitesEvaluation(string $modalitesEvaluation): self
    {
        $this->modalitesEvaluation = $modalitesEvaluation;

        return $this;
    }

    public function getAvatar()
    {
        return $this->avatar;
    }

    public function setAvatar($avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getStatutBrief(): ?string
    {
        return $this->statutBrief;
    }

    public function setStatutBrief(string $statutBrief): self
    {
        $this->statutBrief = $statutBrief;

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
        }

        return $this;
    }

    public function removeGroupe(Groupes $groupe): self
    {
        if ($this->groupes->contains($groupe)) {
            $this->groupes->removeElement($groupe);
        }

        return $this;
    }

    public function getFormateur(): ?Formateur
    {
        return $this->formateur;
    }

    public function setFormateur(?Formateur $formateur): self
    {
        $this->formateur = $formateur;

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
     * @return Collection|Niveau[]
     */
    public function getNiveaux(): Collection
    {
        return $this->niveaux;
    }

    public function addNiveau(Niveau $niveau): self
    {
        if (!$this->niveaux->contains($niveau)) {
            $this->niveaux[] = $niveau;
            $niveau->setBrief($this);
        }

        return $this;
    }

    public function removeNiveau(Niveau $niveau): self
    {
        if ($this->niveaux->contains($niveau)) {
            $this->niveaux->removeElement($niveau);
            // set the owning side to null (unless already changed)
            if ($niveau->getBrief() === $this) {
                $niveau->setBrief(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->contains($tag)) {
            $this->tags->removeElement($tag);
        }

        return $this;
    }

    /**
     * @return Collection|Ressource[]
     */
    public function getRessources(): Collection
    {
        return $this->ressources;
    }

    public function addRessource(Ressource $ressource): self
    {
        if (!$this->ressources->contains($ressource)) {
            $this->ressources[] = $ressource;
            $ressource->setBrief($this);
        }

        return $this;
    }

    public function removeRessource(Ressource $ressource): self
    {
        if ($this->ressources->contains($ressource)) {
            $this->ressources->removeElement($ressource);
            // set the owning side to null (unless already changed)
            if ($ressource->getBrief() === $this) {
                $ressource->setBrief(null);
            }
        }

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
            $promoBrief->setBrief($this);
        }

        return $this;
    }

    public function removePromoBrief(PromoBrief $promoBrief): self
    {
        if ($this->promoBriefs->contains($promoBrief)) {
            $this->promoBriefs->removeElement($promoBrief);
            // set the owning side to null (unless already changed)
            if ($promoBrief->getBrief() === $this) {
                $promoBrief->setBrief(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|LivrableAttendu[]
     */
    public function getLivrableAttendus(): Collection
    {
        return $this->livrableAttendus;
    }

    public function addLivrableAttendu(LivrableAttendu $livrableAttendu): self
    {
        if (!$this->livrableAttendus->contains($livrableAttendu)) {
            $this->livrableAttendus[] = $livrableAttendu;
            $livrableAttendu->addBrief($this);
        }

        return $this;
    }

    public function removeLivrableAttendu(LivrableAttendu $livrableAttendu): self
    {
        if ($this->livrableAttendus->contains($livrableAttendu)) {
            $this->livrableAttendus->removeElement($livrableAttendu);
            $livrableAttendu->removeBrief($this);
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
}
