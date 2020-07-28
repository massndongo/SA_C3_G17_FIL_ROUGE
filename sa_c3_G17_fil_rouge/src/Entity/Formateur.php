<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\FormateurRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"user:read"}},
 *     itemOperations={
 *          "get_formateur"={
 *              "method"="GET",
 *              "path"="/formateurs/{id}",
 *              "requirements"={"id"="\d+"},
 *              "security"="(is_granted('ROLE_ADMIN') or is_granted('ROLE_FORMATEUR'))",
 *              "security_message"="Vous n'avez pas access à cette Ressource"
 *          },
 *          "set_formateur"={
 *              "method"="PUT",
 *              "path"="/formateurs/{id}",
 *              "requirements"={"id"="\d+"},
 *              "security"="(is_granted('ROLE_ADMIN') or is_granted('ROLE_FORMATEUR'))",
 *              "security_message"="Vous n'avez pas access à cette Ressource"
 *          },
 *     },
 *     collectionOperations={
 *          "get_formateurs"={
 *              "method"="GET",
 *              "path"="/formateurs/",
 *              "security"="(is_granted('ROLE_ADMIN') or is_granted('ROLE_FORMATEUR'))",
 *              "security_message"="Vous n'avez pas access à cette Ressource"
 *          },
 *     }
 * )
 * @ORM\Entity(repositoryClass=FormateurRepository::class)
 */
class Formateur extends User
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}
