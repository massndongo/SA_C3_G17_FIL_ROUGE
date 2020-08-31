<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\LivrablePartielRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 *@ApiResource(
 *     normalizationContext={"groups"={"livrablepartiel:read"}},
 *     collectionOperations={
 *          "get_competence_apprenant"={
 *              "path" = "formateurs/promo/{id}/referentiel/{idr}/competences",
 *              "method" = "GET",
 *              "security"="is_granted('ROLE_FORMATEUR')",
 *              "security_message"="Vous n'avez pas access à cette Ressource",
 *          },
 *          
 *     },
 *     itemOperations={
 *          "update_livrable_rendu_by_formateur"={
 *              "path" = "apprenants/{id}/livrablepartiels/{idl}",
 *              "method" ="PUT",
 *              "requirements"={"id"="\d+", "idl"="\d+"},
 *              "security"="is_granted('ROLE_FORMATEUR')",
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
 * @ORM\Entity(repositoryClass=LivrablePartielRepository::class)
 */
class LivrablePartiel
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $libelle;

    /**
     * @ORM\Column(type="date")
     */
    private $delai;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nombreRendu;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDeleted;

    /**
     * @ORM\OneToMany(targetEntity=LivrableRendu::class, mappedBy="livrablePartiel")
     */
    private $livrableRendu;

    /**
     * @ORM\OneToMany(targetEntity=NiveauLivrablePartiel::class, mappedBy="livrablePartiel")
     */
    private $niveauLivrablePartiel;

    public function __construct()
    {
        $this->livrableRendu = new ArrayCollection();
        $this->niveauLivrablePartiel = new ArrayCollection();
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

    public function getDelai(): ?\DateTimeInterface
    {
        return $this->delai;
    }

    public function setDelai(\DateTimeInterface $delai): self
    {
        $this->delai = $delai;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getNombreRendu(): ?int
    {
        return $this->nombreRendu;
    }

    public function setNombreRendu(?int $nombreRendu): self
    {
        $this->nombreRendu = $nombreRendu;

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
     * @return Collection|LivrableRendu[]
     */
    public function getLivrableRendu(): Collection
    {
        return $this->livrableRendu;
    }

    public function addLivrableRendu(LivrableRendu $livrableRendu): self
    {
        if (!$this->livrableRendu->contains($livrableRendu)) {
            $this->livrableRendu[] = $livrableRendu;
            $livrableRendu->setLivrablePartiel($this);
        }

        return $this;
    }

    public function removeLivrableRendu(LivrableRendu $livrableRendu): self
    {
        if ($this->livrableRendu->contains($livrableRendu)) {
            $this->livrableRendu->removeElement($livrableRendu);
            // set the owning side to null (unless already changed)
            if ($livrableRendu->getLivrablePartiel() === $this) {
                $livrableRendu->setLivrablePartiel(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|NiveauLivrablePartiel[]
     */
    public function getNiveauLivrablePartiel(): Collection
    {
        return $this->niveauLivrablePartiel;
    }

    public function addNiveauLivrablePartiel(NiveauLivrablePartiel $niveauLivrablePartiel): self
    {
        if (!$this->niveauLivrablePartiel->contains($niveauLivrablePartiel)) {
            $this->niveauLivrablePartiel[] = $niveauLivrablePartiel;
            $niveauLivrablePartiel->setLivrablePartiel($this);
        }

        return $this;
    }

    public function removeNiveauLivrablePartiel(NiveauLivrablePartiel $niveauLivrablePartiel): self
    {
        if ($this->niveauLivrablePartiel->contains($niveauLivrablePartiel)) {
            $this->niveauLivrablePartiel->removeElement($niveauLivrablePartiel);
            // set the owning side to null (unless already changed)
            if ($niveauLivrablePartiel->getLivrablePartiel() === $this) {
                $niveauLivrablePartiel->setLivrablePartiel(null);
            }
        }

        return $this;
    }
}
