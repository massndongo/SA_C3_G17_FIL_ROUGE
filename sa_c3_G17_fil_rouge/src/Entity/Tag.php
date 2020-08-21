<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\TagRepository;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 * attributes={
 *      "normalization_context"={"groups"={"tag:read"}}
 * },
 *  collectionOperations={
 *      "post"={
 *          "path"="admin/tags",
 *          "security"="is_granted('ROLE_FORMATEUR')",
 *           "security_message"="Vous n'avez pas le droit",
 *      },
 *      "get"={
 *          "path"="admin/tags",
 *          "security"="is_granted('ROLE_FORMATEUR')",
 *           "security_message"="Vous n'avez pas le droit",
 *      }
 * },
 * itemOperations={
 *      "get"={
 *          "path"="admin/tags/{id}",
 *          "security"="is_granted('ROLE_FORMATEUR')",
 *           "security_message"="Vous n'avez pas le droit",
 *      },
 *      "put"={
 *         "path"="admin/tags/{id}" ,
 *         "security"="is_granted('ROLE_FORMATEUR')",
 *           "security_message"="Vous n'avez pas le droit",
 *      }
 * }
 * )
 * @ORM\Entity(repositoryClass=TagRepository::class)
 */
class Tag
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"tag:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"tag:read"})
     */
    
    private $libelle;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"tag:read"})
     */
    private $descriptif;

    /**
     * @ORM\ManyToMany(targetEntity=GroupeTag::class, mappedBy="tags")
     * @Groups({"tag:read"})
     * ApiSubresource
     */
    private $groupeTags;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isDeleted;

    public function __construct()
    {
        $this->groupeTags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection|GroupeTag[]
     */
    public function getGroupeTags(): Collection
    {
        return $this->groupeTags;
    }

    public function addGroupeTag(GroupeTag $groupeTag): self
    {
        if (!$this->groupeTags->contains($groupeTag)) {
            $this->groupeTags[] = $groupeTag;
            $groupeTag->addTag($this);
        }

        return $this;
    }

    public function removeGroupeTag(GroupeTag $groupeTag): self
    {
        if ($this->groupeTags->contains($groupeTag)) {
            $this->groupeTags->removeElement($groupeTag);
            $groupeTag->removeTag($this);
        }

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(?bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }
}
