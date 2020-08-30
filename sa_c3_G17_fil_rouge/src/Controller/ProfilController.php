<?php

namespace App\Controller;

use App\Entity\Profil;
use App\Repository\ProfilRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProfilController extends AbstractController
{
    private $profilRepository,
            $serializer;

    private const ACCESS_DENIED = "Vous n'avez pas accés à cette ressource.",
        RESOURCE_NOT_FOUND = "Ressource inexistante.",
        PROFIL_READ = "profil:read",
        PROFIL_USERS = "profilUsers:read";

    public function __construct(ProfilRepository $profilRepository,SerializerInterface $serializer)
    {
        $this->profilRepository = $profilRepository;
        $this->serializer = $serializer;
    }

    /**
     * @Route(
     *     path="/api/admins/profils",
     *     methods={"GET"},
     * )
     */
    public function getProfils()
    {
        if (!$this->isGranted("VIEW",new Profil()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $profils = $this->profilRepository->findBy([
            "isDeleted" => false
        ]);
        $profils = $this->serializer->normalize($profils,null,["groups" => [self::PROFIL_READ]]);
        return $this->json($profils,Response::HTTP_OK);
    }

    /**
     * @Route(
     *     path="/api/admins/profils/{id<\d+>}/users",
     *     methods={"GET"},
     * )
     */
    public function getUsersInProfil($id,ProfilRepository $profilRepository)
    {
        if (!$this->isGranted("VIEW",new Profil()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $profil = $profilRepository->findOneBy(["id" => $id]);
        if($profil && !$profil->getIsDeleted()){
            $profil = $this->serializer->normalize($profil,null,["groups" => [self::PROFIL_USERS]]);
            return $this->json($profil,Response::HTTP_OK);
        }
        return $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/admins/profils",
     *     methods={"POST"},
     *
     * )
     */
    public function addProfil(Request $request,EntityManagerInterface $manager,ValidatorInterface $validator)
    {
        if (!$this->isGranted("ADD",new Profil()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $profilJson = $request->getContent();
        $profilTab = $this->serializer->decode($profilJson,"json");
        $profil = $this->serializer->denormalize($profilTab,"App\Entity\Profil");

        $profil->setIsDeleted(false);
        $errors = $validator->validate($profil);
        if (count($errors)){
            return new JsonResponse($errors,Response::HTTP_BAD_REQUEST,[],true);
        }
        $oldProfil = $this->profilRepository->findOneBy(["libelle" => $profil->getLibelle()]);
        if (!$oldProfil)
        {
            $libelle = strtoupper($profil->getLibelle());
            $profil->setLibelle($libelle);
            $manager->persist($profil);
            $manager->flush();
            $profil = $this->serializer->normalize($profil,null,["groups" => [self::PROFIL_READ]]);
            return $this->json($profil,Response::HTTP_CREATED);
        }else{
            $oldProfil = $this->serializer->normalize($oldProfil,null,["groups" => [self::PROFIL_READ]]);
            return $this->json($oldProfil,Response::HTTP_OK);
        }
    }

    /**
     * @Route(
     *     path="/api/admins/profils/{id<\d+>}",
     *     methods={"GET"},
     * )
     */
    public function getProfil($id)
    {
        if (!$this->isGranted("VIEW",new Profil()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $profil = $this->profilRepository->findOneBy([
            "id" => $id
        ]);
        if($profil && !$profil->getIsDeleted()){
            $profil = $this->serializer->normalize($profil,null,["groups" => [self::PROFIL_READ]]);
            return $this->json($profil,Response::HTTP_OK);
        }
        return $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/admins/profils/{id<\d+>}",
     *     methods={"PUT"},
     * )
     */
    public function setProfil(Profil $oldProfil,EntityManagerInterface $manager,Request $request,ValidatorInterface $validator)
    {
        if (!$this->isGranted("EDIT",new Profil()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $profilJson = $request->getContent();
        $profilTab = $this->serializer->decode($profilJson,"json");
        $profil = $this->serializer->denormalize($profilTab,"App\Entity\Profil");
        $errors = $validator->validate($profil);
        if(count($errors))
        {
            return $this->json($errors,Response::HTTP_BAD_REQUEST);
        }
        $libelle = strtoupper($profil->getLibelle());
        $profil->setLibelle($libelle);
        $profiExist = $this->profilRepository->findOneBy(["libelle" => $libelle]);
        if ($profiExist)
        {
            return $this->json(["message" => "Un profil ayant ce libelle existe déjà."],Response::HTTP_BAD_REQUEST);
        }else{
            $isDeleted = $profil->getIsDeleted() == true ? true : false;
            $oldProfil->setLibelle($profil->getlibelle())
                    ->setIsDeleted($isDeleted);
            $manager->flush();
            $oldProfil = $this->serializer->normalize($oldProfil,null,["groups" => [self::PROFIL_READ]]);
            return $this->json($oldProfil,Response::HTTP_OK);
        }
        return $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/admins/profils/{id<\d+>}",
     *     methods={"DELETE"},
     * )
     */
    public function deleteProfil(Profil $profil,EntityManagerInterface $manager)
    {
        if (!$this->isGranted("DELETE",new Profil()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $profil->setIsDeleted(true);
        $manager->flush();
        $profil = $this->serializer->normalize($profil,null,["groups" => [self::PROFIL_READ]]);
        return $this->json($profil,Response::HTTP_OK);
    }
}
