<?php

namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ReferentielRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"referentiel:read"}},
 * )
 * @ORM\Entity(repositoryClass=ReferentielRepository::class)
 */
class Referentiel
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"promos:read","referentiel:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"promos:read","referentiel:read"})
     */
    private $libelle;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"promos:read","referentiel:read"})
     */
    private $presentation;

    /**
     * @ORM\Column(type="blob", length=255, nullable=true)
     * @Groups({"promos:read","referentiel:read"})
     */
    private $programme;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"promos:read","referentiel:read"})
     */
    private $critereAdmission;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"promos:read","referentiel:read"})
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

    /**
     * @ORM\OneToMany(targetEntity=Brief::class, mappedBy="referentiel")
     */
    private $briefs;

    /**
     * @ORM\OneToMany(targetEntity=StatistiquesCompetences::class, mappedBy="referentiel")
     */
    private $statistiquesCompetences;

    public function __construct()
    {
        $this->groupeCompetence = new ArrayCollection();
        $this->promos = new ArrayCollection();
        $this->briefs = new ArrayCollection();
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

    /**
     * @return Collection|Brief[]
     */
    public function getBriefs(): Collection
    {
        return $this->briefs;
    }

    public function addBrief(Brief $brief): self
    {
        if (!$this->briefs->contains($brief)) {
            $this->briefs[] = $brief;
            $brief->setReferentiel($this);
        }

        return $this;
    }

    public function removeBrief(Brief $brief): self
    {
        if ($this->briefs->contains($brief)) {
            $this->briefs->removeElement($brief);
            // set the owning side to null (unless already changed)
            if ($brief->getReferentiel() === $this) {
                $brief->setReferentiel(null);
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
            $statistiquesCompetence->setReferentiel($this);
        }

        return $this;
    }

    public function removeStatistiquesCompetence(StatistiquesCompetences $statistiquesCompetence): self
    {
        if ($this->statistiquesCompetences->contains($statistiquesCompetence)) {
            $this->statistiquesCompetences->removeElement($statistiquesCompetence);
            // set the owning side to null (unless already changed)
            if ($statistiquesCompetence->getReferentiel() === $this) {
                $statistiquesCompetence->setReferentiel(null);
            }
        }

        return $this;
    }
}
