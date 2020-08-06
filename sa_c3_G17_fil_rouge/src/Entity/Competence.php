<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\CompetenceRepository;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ApiResource(
 *      normalizationContext={"groups"={"grpe:read"}},
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
     * @Groups({"grpe:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(
     *     message="Le libelle est obligatoire"
     * )
     * @Groups({"grpe:read"})
     */
    private $libelleC;

    /**
     * @ORM\ManyToMany(targetEntity=GroupeCompetence::class, inversedBy="competences")
     * @Assert\NotBlank(
     *     message="Une competence est dans au moins un groupe de competence"
     * )
     * @ApiSubresource()
     */
    private $groupeCompetence;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"grpe:read"})
     */
    private $isDeleted;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(
     *     message="Le descriptif est obligatoire"
     * )
     * @Groups({"grpe:read"})
     */
    private $descriptifC;

    public function __construct()
    {
        $this->groupeCompetence = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelleC;
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
        return $this->descriptifC;
    }

    public function setDescriptif(string $descriptif): self
    {
        $this->descriptif = $descriptif;

        return $this;
    }
}
