<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ApprenantRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource(
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
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}
