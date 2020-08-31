<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\StatistiquesCompetencesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass=StatistiquesCompetencesRepository::class)
 */
class StatistiquesCompetences
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $niveau1;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $niveau2;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $niveau3;

    /**
     * @ORM\ManyToOne(targetEntity=Apprenant::class, inversedBy="statisques")
     */
    private $apprenant;

    /**
     * @ORM\ManyToOne(targetEntity=Competence::class, inversedBy="statistiquesCompetences")
     */
    private $Competence;

    /**
     * @ORM\ManyToOne(targetEntity=Referentiel::class, inversedBy="statistiquesCompetences")
     */
    private $referentiel;

    /**
     * @ORM\ManyToOne(targetEntity=Promos::class, inversedBy="statistiquesCompetences")
     */
    private $promos;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNiveau1(): ?bool
    {
        return $this->niveau1;
    }

    public function setNiveau1(?bool $niveau1): self
    {
        $this->niveau1 = $niveau1;

        return $this;
    }

    public function getNiveau2(): ?bool
    {
        return $this->niveau2;
    }

    public function setNiveau2(?bool $niveau2): self
    {
        $this->niveau2 = $niveau2;

        return $this;
    }

    public function getNiveau3(): ?bool
    {
        return $this->niveau3;
    }

    public function setNiveau3(?bool $niveau3): self
    {
        $this->niveau3 = $niveau3;

        return $this;
    }

    public function getApprenant(): ?Apprenant
    {
        return $this->apprenant;
    }

    public function setApprenant(?Apprenant $apprenant): self
    {
        $this->apprenant = $apprenant;

        return $this;
    }

    public function getCompetence(): ?Competence
    {
        return $this->Competence;
    }

    public function setCompetence(?Competence $Competence): self
    {
        $this->Competence = $Competence;

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

    public function getPromos(): ?Promos
    {
        return $this->promos;
    }

    public function setPromos(?Promos $promos): self
    {
        $this->promos = $promos;

        return $this;
    }
}
