<?php

namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use App\Repository\ReferentielRepository;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *      
 *     normalizationContext={"groups"={"ref:read"}},
 *     collectionOperations={
 *          "get_referentiel"={
 *              "method" = "GET",
 *              "path" = "/admin/referentiels",
 *              "normalization_context"={"groups"={"ref:read"}},
 *              "security"="is_granted('VIEW',object)",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *          },
 *          "get_grpecompetences"={
 *              "method" = "GET",
 *              "path" = "/admin/referentiels/grpecompetences",
 *              "access_control"="is_granted('VIEW',object)",
 *              "access_control_message"="Vous n'avez pas access à cette Ressource",
 *          },
 *          "add_referentiel"={
 *              "method" = "POST",
 *              "path" = "/admin/referentiels",
 *              "security_post_denormalize"="is_granted('EDIT',object)",
 *              "security_post_denormalize_message"="Vous n'avez pas access à cette Ressource",
 *          },
 *     },
 *     itemOperations={
 *          "get_referentiel"={
 *              "method" = "GET",
 *              "path" = "/admin/referentiels/{id}",
 *              "requirements"={"id"="\d+"},
 *              "security"="(is_granted('ROLE_FORMATEUR') or is_granted('ROLE_APPRENANT') or is_granted('ROLE_CM'))",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *          },
 *          "get_grpecompetence_in_referentiel"={
 *              "method" = "GET",
 *              "path" = "/admin/referentiels/{id}/grpecompetences",
 *              "requirements"={"id"="\d+"},
 *              "security"="(is_granted('ROLE_FORMATEUR') or is_granted('ROLE_APPRENANT') or is_granted('ROLE_CM'))",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *          },
 *          "set_referentiel"={
 *              "method" = "PUT",
 *              "path" = "/admin/referentiels/{id}",
 *              "requirements"={"id"="\d+"},
 *              "security"="(is_granted('ROLE_FORMATEUR') or is_granted('ROLE_APPRENANT') or is_granted('ROLE_CM'))",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *          },
 *  *          "del_referentiel"={
 *              "method" = "DELETE",
 *              "path" = "/admin/referentiels/{id}",
 *              "requirements"={"id"="\d+"},
 *              "security_post_denormalize"="is_granted('DEL',object)",
 *              "security_post_denormalize_message"="Vous n'avez pas access à cette Ressource",
 *          }
 *     }
 * )
 * @ORM\Entity(repositoryClass=ReferentielRepository::class)
 */
class Referentiel
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("ref:read")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("ref:read")
     * @Assert\NotBlank(message="Le username est obligatoire")
     */
    private $libelle;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("ref:read")
     * @Assert\NotBlank(message="Le username est obligatoire")
     */
    private $presentation;

    /**
     * @ORM\Column(type="blob", nullable=true)
     * @Assert\NotBlank(message="Le username est obligatoire")
     */
    private $programme;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("ref:read")
     * @Assert\NotBlank(message="Le username est obligatoire")
     */
    private $critereAdmission;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("ref:read")
     * @Assert\NotBlank(message="Le username est obligatoire")
     */
    private $critereEvaluation;

    /**
     * @ORM\ManyToMany(targetEntity=GroupeCompetence::class, inversedBy="referentiels",cascade={"persist"})
     * @Groups("ref:read")
     * @Assert\NotBlank(message="Le username est obligatoire")
     */
    private $groupeCompetence;

    /**
     * @ORM\Column(type="boolean")
     * @Groups("ref:read")
     */
    private $isDeleted;

    /**
     * @ORM\OneToMany(targetEntity=Promos::class, mappedBy="referentiel")
     */
    private $promos;

    public function __construct()
    {
        $this->groupeCompetence = new ArrayCollection();
        $this->promos = new ArrayCollection();
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

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getPresentation(): ?string
    {
        return $this->presentation;
    }

    public function setPresentation(string $presentation): self
    {
        $this->presentation = $presentation;

        return $this;
    }

    public function getProgramme()
    {
        return $this->programme;
    }

    public function setProgramme($programme): self
    {
        $this->programme = $programme;

        return $this;
    }

    public function getCritereAdmission(): ?string
    {
        return $this->critereAdmission;
    }

    public function setCritereAdmission(string $critereAdmission): self
    {
        $this->critereAdmission = $critereAdmission;

        return $this;
    }

    public function getCritereEvaluation(): ?string
    {
        return $this->critereEvaluation;
    }

    public function setCritereEvaluation(string $critereEvaluation): self
    {
        $this->critereEvaluation = $critereEvaluation;

        return $this;
    }

    /**
     * @return Collection|groupeCompetence[]
     */
    public function getGroupeCompetence(): Collection
    {
        return $this->groupeCompetence;
    }

    public function addGroupeCompetence(groupeCompetence $groupeCompetence): self
    {
        if (!$this->groupeCompetence->contains($groupeCompetence)) {
            $this->groupeCompetence[] = $groupeCompetence;
            $groupeCompetence->addReferentiel($this);
        }

        return $this;
    }

    public function removeGroupeCompetence(groupeCompetence $groupeCompetence): self
    {
        if ($this->groupeCompetence->contains($groupeCompetence)) {
            $this->groupeCompetence->removeElement($groupeCompetence);
            $groupeCompetence->removeReferentiel($this);
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

    /**
     * @return Collection|Promos[]
     */
    public function getPromos(): Collection
    {
        return $this->promos;
    }

    public function addPromo(Promos $promo): self
    {
        if (!$this->promos->contains($promo)) {
            $this->promos[] = $promo;
            $promo->setReferentiel($this);
        }

        return $this;
    }

    public function removePromo(Promos $promo): self
    {
        if ($this->promos->contains($promo)) {
            $this->promos->removeElement($promo);
            // set the owning side to null (unless already changed)
            if ($promo->getReferentiel() === $this) {
                $promo->setReferentiel(null);
            }
        }

        return $this;
    }
}
