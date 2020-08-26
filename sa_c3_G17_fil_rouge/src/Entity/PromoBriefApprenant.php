<?php

namespace App\Entity;

use App\Repository\PromoBriefApprenantRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PromoBriefApprenantRepository::class)
 */
class PromoBriefApprenant
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
    private $statut;

    /**
     * @ORM\ManyToOne(targetEntity=Apprenant::class, inversedBy="promoBriefApprenants")
     */
    private $apprenant;

    /**
     * @ORM\ManyToOne(targetEntity=PromoBrief::class, inversedBy="promoBriefApprenants")
     */
    private $prommoBrief;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;

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

    public function getPrommoBrief(): ?PromoBrief
    {
        return $this->prommoBrief;
    }

    public function setPrommoBrief(?PromoBrief $prommoBrief): self
    {
        $this->prommoBrief = $prommoBrief;

        return $this;
    }
}
