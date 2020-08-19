<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\CompetenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"competence:read"}},
 *     collectionOperations={
 *          "get"={
 *              "path" = "/admin/competences",
 *              "security"="is_granted('ROLE_FORMATEUR')",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *          },
 *          "post"={
 *              "security_post_denormalize"="is_granted('EDIT',object)",
 *              "security_post_denormalize_message"="Vous n'avez pas access à cette Ressource",
 *              "path" = "/admin/competences",

 *          },
 *     },
 *     itemOperations={
 *          "get"={
 *              "path" = "/admin/competences/{id}",
 *              "requirements"={"id"="\d+"},
 *              "security"="is_granted('VIEW',object)",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *          },
 *          "set_competence"={
 *              "method" = "PUT",
 *              "path" = "/admin/competences/{id}",
 *              "requirements"={"id"="\d+"},
 *              "security"="is_granted('EDIT',object)",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *          },
 *     }
 * )
 * @ORM\Entity(repositoryClass=CompetenceRepository::class)
 */
class Competence
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"grpecompetence:read_m","competence:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(
     *     message="Le libelle est obligatoire"
     * )
     * @Groups({"grpecompetence:read_m","competence:read"})
     */
    private $libelle;

    /**
     * @ORM\ManyToMany(targetEntity=GroupeCompetence::class, inversedBy="competences",cascade={"persist"})
     * @Assert\NotBlank(
     *     message="Une competence est dans au moins un groupe de competence"
     * )
     * @Groups({"grpecompetence:read_m"})
     */
    private $groupeCompetence;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDeleted;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(
     *     message="Le descriptif est obligatoire"
     * )
     * @Groups({"grpecompetence:read_m","competence:read"})
     */
    private $descriptif;

    /**
     * @ORM\OneToMany(targetEntity=Niveau::class, mappedBy="competence")
     * @Assert\NotNull(
     *     message="Les niveaux d'évaluation sont obligatoires"
     * )
     * @Groups({"grpecompetence:read_m","competence:read"})
     */
    private $niveaux;

    /**
     * @ORM\OneToMany(targetEntity=StatistiquesCompetences::class, mappedBy="competence")
     */
    private $statistiquesCompetences;

    public function __construct()
    {
        $this->groupeCompetence = new ArrayCollection();
        $this->niveaux = new ArrayCollection();
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

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @return Collection|GroupeCompetence[]
     */
    public function getGroupeCompetence(): Collection
    {
        return $this->groupeCompetence;
    }

    public function addGroupeCompetence(GroupeCompetence $groupeCompetence): self
    {
        if (!$this->groupeCompetence->contains($groupeCompetence)) {
            $this->groupeCompetence[] = $groupeCompetence;
        }

        return $this;
    }

    public function removeGroupeCompetence(GroupeCompetence $groupeCompetence): self
    {
        if ($this->groupeCompetence->contains($groupeCompetence)) {
            $this->groupeCompetence->removeElement($groupeCompetence);
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

    public function getDescriptif(): ?string
    {
        return $this->descriptif;
    }

    public function setDescriptif(string $descriptif): self
    {
        $this->descriptif = $descriptif;

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
            $niveau->setCompetence($this);
        }

        return $this;
    }

    public function removeNiveau(Niveau $niveau): self
    {
        if ($this->niveaux->contains($niveau)) {
            $this->niveaux->removeElement($niveau);
            // set the owning side to null (unless already changed)
            if ($niveau->getCompetence() === $this) {
                $niveau->setCompetence(null);
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
            $statistiquesCompetence->setCompetence($this);
        }

        return $this;
    }

    public function removeStatistiquesCompetence(StatistiquesCompetences $statistiquesCompetence): self
    {
        if ($this->statistiquesCompetences->contains($statistiquesCompetence)) {
            $this->statistiquesCompetences->removeElement($statistiquesCompetence);
            // set the owning side to null (unless already changed)
            if ($statistiquesCompetence->getCompetence() === $this) {
                $statistiquesCompetence->setCompetence(null);
            }
        }

        return $this;
    }
}
