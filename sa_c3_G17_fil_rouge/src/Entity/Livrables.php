<?php

namespace App\Entity;

use App\Repository\LivrablesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LivrablesRepository::class)
 */
class Livrables
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $url;

    /**
     * @ORM\ManyToOne(targetEntity=LivrableAttendu::class, inversedBy="livrables",cascade={"persist"})
     */
    private $livrableAttendu;

    /**
     * @ORM\ManyToOne(targetEntity=Apprenant::class, inversedBy="livrables")
     */
    private $apprenant;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getLivrableAttendu(): ?LivrableAttendu
    {
        return $this->livrableAttendu;
    }

    public function setLivrableAttendu(?LivrableAttendu $livrableAttendu): self
    {
        $this->livrableAttendu = $livrableAttendu;

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
