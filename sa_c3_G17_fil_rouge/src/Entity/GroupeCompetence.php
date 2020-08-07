<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\GroupeCompetenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"grpecompetence:read_m"}},
 *     collectionOperations={
 *          "get_grpeCompetences"={
 *              "method" = "GET",
 *              "path" = "/admin/grpecompetences",
 *              "security"="is_granted('ROLE_FORMATEUR')",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *          },
 *          "get_competences"={
 *              "method" = "GET",
 *              "path" = "/admin/grpecompetences/competences",
 *              "access_control"="is_granted('ROLE_FORMATEUR')",
 *              "access_control_message"="Vous n'avez pas access à cette Ressource",
 *          },
 *          "add_groupeCompetence"={
 *              "method" = "POST",
 *              "path" = "/admin/grpecompetences",
 *              "security_post_denormalize"="is_granted('EDIT',object)",
 *              "security_post_denormalize_message"="Vous n'avez pas access à cette Ressource",
 *          },
 *     },
 *     itemOperations={
 *          "get_groupeCompetence"={
 *              "method" = "GET",
 *              "path" = "/admin/grpecompetences/{id}",
 *              "requirements"={"id"="\d+"},
 *              "security"="is_granted('VIEW',object)",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *          },
 *          "get_competence_in_grpeCompetence"={
 *              "method" = "GET",
 *              "path" = "/admin/grpecompetences/{id}/competences",
 *              "requirements"={"id"="\d+"},
 *              "security"="is_granted('VIEW',object)",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *          },
 *          "set_grpeCompetence"={
 *              "method" = "PUT",
 *              "path" = "/admin/grpecompetences/{id}",
 *              "requirements"={"id"="\d+"},
 *              "security"="is_granted('SET',object)",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *          },
 *     }
 * )
 * @ORM\Entity(repositoryClass=GroupeCompetenceRepository::class)
 */
class GroupeCompetence
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"grpecompetence:read_m"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Groups({"grpecompetence:read_m"})
     */
    private $libelle;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Groups({"grpecompetence:read_m"})
     */
    private $descriptif;

    /**
     * @ORM\ManyToOne(targetEntity=Admin::class, inversedBy="groupeCompetences")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank()
     *
     */
    private $administrateur;

    /**
     * @ORM\ManyToMany(targetEntity=Competence::class, mappedBy="groupeCompetence",cascade={"persist"})
     * @Assert\NotNull()
     * @Groups({"grpecompetence:read_m"})
     */
    private $competences;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDeleted;

    /**
     * @ORM\ManyToMany(targetEntity=Referentiel::class, mappedBy="groupeCompetence")
     */
    private $referentiels;

    public function __construct()
    {
        $this->competences = new ArrayCollection();
        $this->referentiels = new ArrayCollection();
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

    public function getDescriptif(): ?string
    {
        return $this->descriptif;
    }

    public function setDescriptif(string $descriptif): self
    {
        $this->descriptif = $descriptif;

        return $this;
    }

    public function getAdministrateur(): ?Admin
    {
        return $this->administrateur;
    }

    public function setAdministrateur(?Admin $administrateur): self
    {
        $this->administrateur = $administrateur;

        return $this;
    }

    /**
     * @return Collection|Competence[]
     */
    public function getCompetences(): Collection
    {
        return $this->competences;
    }

    public function addCompetence(Competence $competence): self
    {
        if (!$this->competences->contains($competence)) {
            $this->competences[] = $competence;
            $competence->addGroupeCompetence($this);
        }

        return $this;
    }

    public function removeCompetence(Competence $competence): self
    {
        if ($this->competences->contains($competence)) {
            $this->competences->removeElement($competence);
            $competence->removeGroupeCompetence($this);
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
     * @return Collection|Referentiel[]
     */
    public function getReferentiels(): Collection
    {
        return $this->referentiels;
    }

    public function addReferentiel(Referentiel $referentiel): self
    {
        if (!$this->referentiels->contains($referentiel)) {
            $this->referentiels[] = $referentiel;
            $referentiel->addGroupeCompetence($this);
        }

        return $this;
    }

    public function removeReferentiel(Referentiel $referentiel): self
    {
        if ($this->referentiels->contains($referentiel)) {
            $this->referentiels->removeElement($referentiel);
            $referentiel->removeGroupeCompetence($this);
        }

        return $this;
    }
}
