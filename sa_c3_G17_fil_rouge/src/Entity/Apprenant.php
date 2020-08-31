<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ApprenantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"user:read"}},
 *     itemOperations={
 *          "get_student"={
 *              "method"="GET",
 *              "path"="/apprenants/{id}",
 *              "requirements"={"id"="\d+"},
 *              "security"="(is_granted('ROLE_ADMIN'))",
 *              "security_message"="Vous n'avez pas access à cette Ressource"
 *          },
 *          "set_student"={
 *              "method"="PUT",
 *              "path"="/apprenants/{id}",
 *              "requirements"={"id"="\d+"},
 *              "security"="(is_granted('ROLE_ADMIN') or is_granted('ROLE_FORMATEUR')) or is_granted('ROLE_CM') or is_granted('ROLE_APPRENANT'))",
 *              "security_message"="Vous n'avez pas access à cette Ressource"
 *          }
 *     },
 *     collectionOperations={
 *          "add_student"={
 *              "method"="POST",
 *              "path"="/apprenants",
 *              "security"="is_granted('ROLE_ADMIN')",
 *              "security_message"="Vous n'avez pas access à cette Ressource"
 *          },
 *          "get_students"={
 *              "method"="GET",
 *              "path"="/apprenants",
 *              "security"="is_granted('ROLE_ADMIN') or is_granted('ROLE_FORMATEUR)",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *
 *          },
 *     }
 * )
 * @ORM\Entity(repositoryClass=ApprenantRepository::class)
 */
class Apprenant extends User
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity=Groupes::class, mappedBy="apprenant")
     */
    private $groupes;

    /**
     * @ORM\OneToMany(targetEntity=LivrableRendu::class, mappedBy="apprenan")
     */
    private $livrableRendus;

    /**
     * @ORM\OneToMany(targetEntity=StatistiquesCompetences::class, mappedBy="apprenant")
     */
    private $statisques;

    /**
     * @ORM\OneToMany(targetEntity=PromoBriefApprenant::class, mappedBy="apprenant")
     */
    private $PromoBriefApprenant;

    public function __construct()
    {
        parent::__construct();
        $this->groupes = new ArrayCollection();
        $this->livrableRendus = new ArrayCollection();
        $this->statisques = new ArrayCollection();
        $this->PromoBriefApprenant = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
            $groupe->addApprenant($this);
        }

        return $this;
    }

    public function removeGroupe(Groupes $groupe): self
    {
        if ($this->groupes->contains($groupe)) {
            $this->groupes->removeElement($groupe);
            $groupe->removeApprenant($this);
        }

        return $this;
    }

    /**
     * @return Collection|LivrableRendu[]
     */
    public function getLivrableRendus(): Collection
    {
        return $this->livrableRendus;
    }

    public function addLivrableRendu(LivrableRendu $livrableRendu): self
    {
        if (!$this->livrableRendus->contains($livrableRendu)) {
            $this->livrableRendus[] = $livrableRendu;
            $livrableRendu->setApprenan($this);
        }

        return $this;
    }

    public function removeLivrableRendu(LivrableRendu $livrableRendu): self
    {
        if ($this->livrableRendus->contains($livrableRendu)) {
            $this->livrableRendus->removeElement($livrableRendu);
            // set the owning side to null (unless already changed)
            if ($livrableRendu->getApprenan() === $this) {
                $livrableRendu->setApprenan(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|StatistiquesCompetences[]
     */
    public function getStatisques(): Collection
    {
        return $this->statisques;
    }

    public function addStatisque(StatistiquesCompetences $statisque): self
    {
        if (!$this->statisques->contains($statisque)) {
            $this->statisques[] = $statisque;
            $statisque->setApprenant($this);
        }

        return $this;
    }

    public function removeStatisque(StatistiquesCompetences $statisque): self
    {
        if ($this->statisques->contains($statisque)) {
            $this->statisques->removeElement($statisque);
            // set the owning side to null (unless already changed)
            if ($statisque->getApprenant() === $this) {
                $statisque->setApprenant(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PromoBriefApprenant[]
     */
    public function getPromoBriefApprenant(): Collection
    {
        return $this->PromoBriefApprenant;
    }

    public function addPromoBriefApprenant(PromoBriefApprenant $promoBriefApprenant): self
    {
        if (!$this->PromoBriefApprenant->contains($promoBriefApprenant)) {
            $this->PromoBriefApprenant[] = $promoBriefApprenant;
            $promoBriefApprenant->setApprenant($this);
        }

        return $this;
    }

    public function removePromoBriefApprenant(PromoBriefApprenant $promoBriefApprenant): self
    {
        if ($this->PromoBriefApprenant->contains($promoBriefApprenant)) {
            $this->PromoBriefApprenant->removeElement($promoBriefApprenant);
            // set the owning side to null (unless already changed)
            if ($promoBriefApprenant->getApprenant() === $this) {
                $promoBriefApprenant->setApprenant(null);
            }
        }

        return $this;
    }
}
