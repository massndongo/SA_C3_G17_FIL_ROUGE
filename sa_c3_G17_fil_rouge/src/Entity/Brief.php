<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\BriefRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ApiResource(
 *      collectionOperations={
 *          "getBriefs" = {
 *              "path" = "/formateurs/briefs",
 *              "method" = "GET",
 *              "normalization_context" = {"groups"={"getBriefs:read"}},
 *              "security"="is_granted('ROLE_FORMATEUR')",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *          },
 *          "getBriefsFormateur" = {
 *              "path" = "/formateurs/promos/{id}/briefs",
 *              "requirements" = {"id"="\d+"},
 *              "method" = "GET",
 *              "normalization_context" = {"groups":"getBriefs:read"},
 *              "security"="is_granted('ROLE_FORMATEUR')",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *          },
 *          "getBriefsInGroupe" = {
 *              "path" = "/formateurs/promos/{idPromo}/groupes/{idGroupe}/briefs",
 *              "requirements" = {"idPromo"="\d+","idGroupe"="\d+"},
 *              "normalization_context" = {"groups"={"getBriefs:read","getBriefsInGroupe:read"}},
 *              "method" = "GET",
 *              "security"="is_granted('ROLE_FORMATEUR')",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *          },
 *          "getBriefsApprenant" = {
 *              "path" = "/apprenants/promos/{id}/briefs",
 *              "requirements" = {"id"="\d+"},
 *              "method" = "GET",
 *              "normalization_context" = {"groups"={"getBriefs:read","getBriefsInGroupe:read"}},
 *              "security"="is_granted('ROLE_FORMATEUR')",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *          },
 *          "getFormateurValideBriefs" = {
 *              "path" = "/formateurs/{id}/briefs/valide",
 *              "requirements" = {"id"="\d+"},
 *              "method" = "GET",
 *              "normalization_context" = {"groups":"getBriefs:read"},
 *              "security"="is_granted('ROLE_FORMATEUR')",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *          },
 *          "getBriefsBrouillonFormateur" = {
 *              "path" = "/formateurs/{id}/briefs/brouillons",
 *              "requirements" = {"id"="\d+"},
 *              "method" = "GET",
 *              "normalization_context" = {"groups":"getBriefs:read"},
 *              "security"="is_granted('ROLE_FORMATEUR')",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *          },
 *          "getBriefInPromo" = {
 *              "path" = "/formateurs/promos/{idPromo}/briefs/{idBrief}",
 *              "requirements" = {"idPromo"="\d+","idBrief"="\d+"},
 *              "normalization_context" = {"groups"={"getBriefs:read","getBriefsInGroupe:read"}},
 *              "method" = "GET",
 *              "security"="is_granted('ROLE_FORMATEUR')",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *          },
 *          "getLivrableRenduApprenant" = {
 *              "path" = "/apprenants/promos/{idPromo}/briefs/{idBrief}",
 *              "requirements" = {"idPromo"="\d+","idBrief"="\d+"},
 *              "normalization_context" = {"groups"={"apprenant:read"}},
 *              "method" = "GET",
 *              "security"="is_granted('ROLE_FORMATEUR')",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *          },
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
 *              "path"="/apprenants/{idStudent}/groupe/{idGroupe}/livrables",
 *              "security"="is_granted('ROLE_FORMATEUR')",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *
 *          },
 *     },
 *
 * )
 * @ORM\Entity(repositoryClass=BriefRepository::class)
 */
//itemOperations={
//    *          "add_assignation"={
//        *              "method"="PUT",
// *              "path" = "/formateurs/promo/{idPromo}/brief/{idBrief}/assignation",
// *              "security" = "is_granted('ROLE_FORMATEUR')",
// *              "security_message" = "Vous n'avez pas access à cette Ressource",
// *          },
// *          "set_brief"={
//        *              "method"="PUT",
// *              "path"="/formateurs/promo/{idP}/brief/{idB}",
// *              "security"="is_granted('ROLE_FORMATEUR')",
// *              "security_message"="Vous n'avez pas access à cette Ressource",
// *          }
// *     },
// the itemOperation generated No item route associated with the type "App\Entity\Brief" error.
// Remove the itemoperation if you want your tests work
class Brief
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"getBriefs:read","apprenant:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"getBriefs:read","apprenant:read"})
     */
    private $langue;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"getBriefs:read","apprenant:read"})
     */
    private $titre;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"getBriefs:read","apprenant:read"})
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"getBriefs:read","apprenant:read"})
     */
    private $contexte;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"getBriefs:read","apprenant:read"})
     */
    private $livrable;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"getBriefs:read","apprenant:read"})
     */
    private $modalitesPedagogiques;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"getBriefs:read","apprenant:read"})
     */
    private $critereDePerformance;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"getBriefs:read","apprenant:read"})
     */
    private $modalitesEvaluation;

    /**
     * @ORM\Column(type="blob", nullable=true)
     */
    private $avatar;

    /**
     * @ORM\Column(type="date")
     * @Groups({"getBriefs:read","apprenant:read"})
     */
    private $dateCreation;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"getBriefs:read","apprenant:read"})
     */
    private $statutBrief;

    /**
     * @ORM\ManyToMany(targetEntity=Groupes::class, inversedBy="briefs")
     * 
     * @Groups({"getBriefsInGroupe:read"})
     */
    private $groupes;

    /**
     * @ORM\ManyToOne(targetEntity=Formateur::class, inversedBy="briefs")
     * @MaxDepth(1)
     * @Groups({"getBriefsInGroupe:read"})
     */
    private $formateur;

    /**
     * @ORM\ManyToOne(targetEntity=Referentiel::class, inversedBy="briefs")
     *
     * @Groups({"getBriefs:read","getBriefsInGroupe:read"})
     */
    private $referentiel;

    /**
     * @ORM\OneToMany(targetEntity=Niveau::class, mappedBy="brief")
     * @MaxDepth(3)
     * @Groups({"getBriefs:read","getBriefsInGroupe:read","apprenant:read"})
     *
     */
    private $niveaux;

    /**
     * @ORM\ManyToMany(targetEntity=Tag::class, inversedBy="briefs")
     * @MaxDepth(1)
     * @Groups({"getBriefs:read"})
     */
    private $tags;

    /**
     * @ORM\OneToMany(targetEntity=Ressource::class, mappedBy="brief")
     * @MaxDepth(1)
     * @Groups({"getBriefs:read"})
     */
    private $ressources;

    /**
     * @ORM\OneToMany(targetEntity=PromoBrief::class, mappedBy="brief")
     * @MaxDepth(1)
     * @Groups({"getBriefs:read","apprenant:read"})
     */
    private $promoBriefs;

    /**
     * @ORM\ManyToMany(targetEntity=LivrableAttendu::class, mappedBy="briefs")
     * 
     * @Groups({"getBriefs:read"})
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
