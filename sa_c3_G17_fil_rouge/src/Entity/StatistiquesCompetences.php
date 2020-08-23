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
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $niveau1;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $niveau2;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $niveau3;

    /**
     * @ORM\ManyToOne(targetEntity=Promos::class, inversedBy="statistiquesCompetences")
     */
    private $promo;

    /**
     * @ORM\ManyToOne(targetEntity=Competence::class, inversedBy="statistiquesCompetences")
     */
    private $competence;

    /**
     * @ORM\ManyToOne(targetEntity=Referentiel::class, inversedBy="statistiquesCompetences")
     */
    private $referentiel;

    /**
     * @ORM\ManyToOne(targetEntity=Apprenant::class, inversedBy="statistiquesCompetences")
     */
    private $apprenant;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNiveau1(): ?int
    {
        return $this->niveau1;
    }

    public function setNiveau1(int $niveau1): self
    {
        $this->niveau1 = $niveau1;

        return $this;
    }

    public function getNiveau2(): ?int
    {
        return $this->niveau2;
    }

    public function setNiveau2(?int $niveau2): self
    {
        $this->niveau2 = $niveau2;

        return $this;
    }

    public function getNiveau3(): ?int
    {
        return $this->niveau3;
    }

    public function setNiveau3(?int $niveau3): self
    {
        $this->niveau3 = $niveau3;

        return $this;
    }

    public function getPromo(): ?Promos
    {
        return $this->promo;
    }

    public function setPromo(?Promos $promo): self
    {
        $this->promo = $promo;

        return $this;
    }

    public function getCompetence(): ?Competence
    {
        return $this->competence;
    }

    public function setCompetence(?Competence $competence): self
    {
        $this->competence = $competence;

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

    public function getApprenant(): ?Apprenant
    {
        return $this->apprenant;
    }

    public function setApprenant(?Apprenant $apprenant): self
    {
        $this->apprenant = $apprenant;

        return $this;
    }
}
