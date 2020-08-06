<?php

namespace App\Controller;

use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Entity\GroupeCompetence;
use App\Repository\AdminRepository;
use App\Repository\GroupeCompetenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;

class GroupeCompetenceController extends AbstractController
{
    /**
     * @Route(
     *     path="/api/admin/grpecompetences",
     *     methods={"GET"},
     *     defaults={
     *          "__controller"="App\Controller\GroupeCompetenceController::getGroupeCompetences",
     *          "__api_resource_class"=GroupeCompetence::class,
     *          "__api_collection_operation_name"="get_grpeCompetences"
     *     }
     * )
     */
    public function getGroupeCompetences(GroupeCompetenceRepository $groupeCompetenceRepository)
    {
        if(!($this->isGranted("ROLE_FORMATEUR")))
            return $this->json(["message" => "Vous n'avez pas access à cette Ressource"],Response::HTTP_FORBIDDEN);
        $groupeCompetences = $groupeCompetenceRepository->findBy([
            "isDeleted" => false
        ]);
        return $this->json($groupeCompetences,Response::HTTP_OK);
    }

    /**
     * @Route(
     *     path="/api/admin/grpecompetences/{id<\d+>}/competences",
     *     methods={"GET"},
     * )
     */
    public function getCompetencesInGroupeCompetence($id,GroupeCompetenceRepository $groupeCompetenceRepository)
    {
        $groupeCompetence = new GroupeCompetence();
        if(!($this->isGranted("VIEW",$groupeCompetence)))
            return $this->json(["message" => "Vous n'avez pas access à cette Ressource"],Response::HTTP_FORBIDDEN);
        $groupeCompetence = $groupeCompetenceRepository->findOneBy([
            "id" => $id
        ]);
        if($groupeCompetence){
            if (!$groupeCompetence->getIsDeleted()){
                $competences = $groupeCompetence->getCompetences();
                return $this->json($competences,Response::HTTP_OK);
            }
        }
        return $this->json(["message" => "Ressource inexistante"],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="admin/grpecompetences/competences",
     *     methods={"GET"},
     *     defaults={
     *          "__controller"="App\Controller\GroupeCompetenceController::getCompetences",
     *          "__api_resource_class"=GroupeCompetence::class,
     *          "__api_collection_operation_name"="get_competences"
     *     }
     * )
     */
    public function getCompetences(GroupeCompetenceRepository $groupeCompetenceRepository)
    {
        $groupeCompetence = new GroupeCompetence();
        if(!($this->isGranted("VIEW",$groupeCompetence)))
            return $this->json(["message" => "Vous n'avez pas access à cette Ressource"],Response::HTTP_FORBIDDEN);
        $groupeCompetence = $groupeCompetenceRepository->findBy([
            "isDeleted" => false
        ]);
        $competences = [];
        $size = count($groupeCompetence);
        for ($i = 0;$i < $size; $i++){
            if(!$groupeCompetence[$i]->getIsDeleted()){
                $competence = $groupeCompetence[$i]->getCompetences();
                $length = count($competence);
                for ($j = 0; $j < $length; $j++){
                    $skill = $competence[$j];
                    if(!$skill->getIsDeleted()){
                        $competences[] = $skill;
                    }
                }
            }
        }
        return $this->json($competences,Response::HTTP_OK);
    }
    
    /**
     * @Route(
     *     path="/api/admin/grpecompetences",
     *     methods={"POST"},
     * )
     */
    public function addGroupeCompetence(TokenStorageInterface $tokenStorage,Request $request,EntityManagerInterface $manager,SerializerInterface $serializer,ValidatorInterface $validator)
    {
        $groupeCompetence = new GroupeCompetence();
        if(!$this->isGranted("EDIT",$groupeCompetence))
            return $this->json(["message" => "Vous n'avez pas access à cette Ressource"],Response::HTTP_FORBIDDEN);
        $groupeCompetence = $request->getContent();
        $administrateur = $tokenStorage->getToken()->getUser();
        $groupeCompetence = $serializer->deserialize($groupeCompetence,"App\Entity\GroupeCompetence",'json');
        $groupeCompetence->setIsDeleted(false)
                         ->setAdministrateur($administrateur);
        $errors = (array)$validator->validate($groupeCompetence);
        if(count($errors)){
            return $this->json($errors,Response::HTTP_BAD_REQUEST);
        }
        if (!count($groupeCompetence->getCompetences())){
            return $this->json(["message" => "Ajoutez au moins une competence a cet groupe de competence."],Response::HTTP_BAD_REQUEST); 
        } 
        $manager->persist($groupeCompetence);
        $manager->flush();
        return $this->json($groupeCompetence,Response::HTTP_CREATED);
    }

    /**
     * @Route(
     *     path="admin/grpecompetences/{id<\d+>}",
     *     methods={"GET"},
     * )
     */
    public function getGroupeCompetence($id,GroupeCompetenceRepository $groupeCompetenceRepository)
    {
        $groupeCompetence = new GroupeCompetence();
        if(!($this->isGranted("VIEW",$groupeCompetence)))
            return $this->json(["message" => "Vous n'avez pas access à cette Ressource"],Response::HTTP_FORBIDDEN);
        $groupeCompetence = $groupeCompetenceRepository->findOneBy([
            "id" => $id
        ]);
        if($groupeCompetence){
            if (!$groupeCompetence->getIsDeleted())
                return $this->json($groupeCompetence,Response::HTTP_OK);
        }
        return $this->json(["message" => "Ressource inexistante"],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="admin/grpecompetences/{id<\d+>}",
     *     methods={"PUT"},
     *     defaults={
     *          "__controller"="App\Controller\GroupeCompetenceController::setGroupeCompetence",
     *          "__api_resource_class"=GroupeCompetence::class,
     *          "__api_item_operation_name"="set_grpeCompetence"
     *     }
     * )
     */
    public function setGroupeCompetence($id,GroupeCompetenceRepository $groupeCompetenceRepository)
    {
        $groupeCompetence = new GroupeCompetence();
        if(!($this->isGranted("EDIT",$groupeCompetence)))
            return $this->json(["message" => "Vous n'avez pas access à cette Ressource"],Response::HTTP_FORBIDDEN);
        $groupeCompetence = $groupeCompetenceRepository->findOneBy([
            "id" => $id
        ]);
        if($groupeCompetence){
            if(!$groupeCompetence->getIsDeleted())
                return $this->json(["message" => "Desolé cette fonctionnalité est en cours de réalisation. Revenez plus tard :)"],Response::HTTP_OK);
        }
        return $this->json(["message" => "Ressource inexistante"],Response::HTTP_NOT_FOUND);
    }
}
