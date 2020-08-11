<?php

namespace App\Controller;

use App\Entity\Profil;
use App\Repository\GroupeCompetenceRepository;
use App\Repository\ProfilRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdminController extends AbstractController
{
    /**
     * @Route(
     *     path="admin/profils",
     *     methods={"GET"},
     *
     * )
     */
    public function getProfils(ProfilRepository $profilRepository)
    {
        $profils = $profilRepository->findBy([
            "isDeleted" => false
        ]);
        return $this->json($profils,Response::HTTP_OK);
    }

    /**
     * @Route(
     *     path="admin/profils/{id<\d+>}/users",
     *     methods={"GET"},
     * )
     */
    public function getUsersInProfil($id,ProfilRepository $profilRepository)
    {
        $profil = $profilRepository->findOneBy([
            "id" => $id
        ]);
        if($profil){
            if(!$profil->getIsDeleted()){
                $users = $profil->getUsers();
                return $this->json($users,Response::HTTP_OK);
            }
        }
        return $this->json(["message" => "Ressource inexistante"],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="admin/profils",
     *     methods={"POST"},
     *
     * )
     */
    public function addProfil(Request $request,EntityManagerInterface $manager,SerializerInterface $serializer,ValidatorInterface $validator)
    {
        $profil = $request->request->all();
        $profil = $serializer->denormalize($profil,"App\Entity\Profil");
        $profil->setIsDeleted(false);
        $errors = $validator->validate($profil);
        if (count($errors)){
            return new JsonResponse($errors,Response::HTTP_BAD_REQUEST,[],true);
        }
        $manager->persist($profil);
        $manager->flush();
        return $this->json($profil,Response::HTTP_CREATED);
    }

    /**
     * @Route(
     *     path="admin/profils/{id<\d+>}",
     *     methods={"GET"},
     * )
     */
    public function getProfil($id,ProfilRepository $profilRepository)
    {
        $profil = $profilRepository->findOneBy([
            "id" => $id
        ]);
        if($profil){
            if (!$profil->getIsDeleted()){
                return $this->json($profil,Response::HTTP_OK);
            }
        }
        return $this->json(["message" => "Ressource inexistante"],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="admin/profils/{id<\d+>}",
     *     methods={"PUT"},
     * )
     */
    public function setProfil($id,ProfilRepository $profilRepository)
    {
        $profil = $profilRepository->findOneBy([
            "id" => $id
        ]);
        if($profil){
            if(!$profil->getIsDeleted())
                return $this->json(["message" => "Desolé cette fonctionnalité est en cours de réalisation. Revenez plus tard :)"],Response::HTTP_OK);
        }
        return $this->json(["message" => "Ressource inexistante"],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="admin/profils/{id<\d+>}",
     *     methods={"DELETE"},
     * )
     */
    public function deleteProfil($id,ProfilRepository $profilRepository,EntityManagerInterface $manager)
    {
        $profil = $profilRepository->findOneBy([
            "id" => $id
        ]);
        if($profil){
            if(!$profil->getIsDeleted()){
                $profil->setIsDeleted(true);
                $manager->flush();
                return $this->json($profil,Response::HTTP_OK);
            }
        }
        return $this->json(["message" => "Ressource inexistante"],Response::HTTP_NOT_FOUND);
    }

}
