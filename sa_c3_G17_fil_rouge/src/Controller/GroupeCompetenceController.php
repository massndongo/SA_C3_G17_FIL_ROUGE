<?php

namespace App\Controller;

use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Entity\Competence;
use App\Entity\GroupeCompetence;
use App\Repository\CompetenceRepository;
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
     *
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
     *     path="/api/admin/grpecompetences/competences",
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
    public function addGroupeCompetence(CompetenceRepository $competenceRepository,TokenStorageInterface $tokenStorage,Request $request,EntityManagerInterface $manager,SerializerInterface $serializer,ValidatorInterface $validator)
    {
        $groupeCompetence = new GroupeCompetence();
        if(!$this->isGranted("EDIT",$groupeCompetence))
            return $this->json(["message" => "Vous n'avez pas access à cette Ressource"],Response::HTTP_FORBIDDEN);
        $groupeCompetenceJson = $request->getContent();
        $administrateur = $tokenStorage->getToken()->getUser();
        $groupeCompetenceTab = $serializer->decode($groupeCompetenceJson,"json");
        $comptences = $groupeCompetenceTab["competences"];
        $groupeCompetenceTab["competences"] = [];
        $groupeCompetenceObj = $serializer->denormalize($groupeCompetenceTab,"App\Entity\GroupeCompetence");
        $groupeCompetenceObj->setIsDeleted(false)
            ->setAdministrateur($administrateur);
        foreach ($comptences as $comptence){

            $skill = $serializer->denormalize($comptence,"App\Entity\Competence");
            $id = $comptence["id"];
            $skill->setId($id);
            $skill->setIsDeleted(false)
                ->addGroupeCompetence($groupeCompetenceObj);
            $error = (array) $validator->validate($skill);
            if (count($error))
                return $this->json($error,Response::HTTP_BAD_REQUEST);
            if($id == null){
                $manager->persist($skill);
            }else{
                $skill = $competenceRepository->findOneBy([
                    "id" => $id
                ]);
            }
            $groupeCompetenceObj->addCompetence($skill);
        }
        $errors = (array)$validator->validate($groupeCompetenceObj);
        if(count($errors)){
            return $this->json($errors,Response::HTTP_BAD_REQUEST);
        }
        if (!count($groupeCompetenceObj->getCompetences()))
            return $this->json(["message" => "Ajoutez au moins une competence à cet groupe de competence."],Response::HTTP_BAD_REQUEST);
        $manager->persist($groupeCompetenceObj);
        $manager->flush();
        return $this->json($groupeCompetenceObj,Response::HTTP_CREATED);
    }

    /**
     * @Route(
     *     path="/api/admin/grpecompetences/{id<\d+>}",
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
     *     path="/api/admin/grpecompetences/{id<\d+>}",
     *     methods={"PUT"},
     *     defaults={
     *          "__controller"="App\Controller\GroupeCompetenceController::setGroupeCompetence",
     *          "__api_resource_class"=GroupeCompetence::class,
     *          "__api_item_operation_name"="set_grpeCompetence"
     *     }
     * )
     */
    public function setGroupeCompetence($id,EntityManagerInterface $manager,GroupeCompetenceRepository $groupeCompetenceRepository,CompetenceRepository $competenceRepository,Request $request,SerializerInterface $serializer,ValidatorInterface $validator)
    {
        $groupeCompetence = new GroupeCompetence();
        if(!$this->isGranted("EDIT",$groupeCompetence))
            return $this->json(["message" => "Vous n'avez pas access à cette Ressource"],Response::HTTP_FORBIDDEN);
        $groupeCompetenceJson = $request->getContent();
        $groupeCompetenceTab = $serializer->decode($groupeCompetenceJson,"json");
        $comptences = $groupeCompetenceTab["competences"];
        $groupeCompetenceTab["competences"] = [];
        $groupeCompetenceObj = $serializer->denormalize($groupeCompetenceTab,"App\Entity\GroupeCompetence");
        $groupeCompetence = $groupeCompetenceRepository->findOneBy([
            "id" => $id
        ]);
        $groupeCompetenceObj->setId((int)$id)
            ->setAdministrateur($groupeCompetence->getAdministrateur())
            ->SetIsDeleted(false);
        if($groupeCompetence){
            if(!$groupeCompetence->getIsDeleted()){
                foreach ($comptences as $comptence){
                    $skill = $serializer->denormalize($comptence,"App\Entity\Competence");
                    $id = $comptence["id"];
                    $skill->setId($id);
                    $skill->setIsDeleted(false)
                        ->addGroupeCompetence($groupeCompetenceObj);
                    $error = (array) $validator->validate($skill);
                    if (count($error))
                        return $this->json($error,Response::HTTP_BAD_REQUEST);
                    if($id == null){
                        $manager->persist($skill);
                    }else{
                        $skill = $competenceRepository->findOneBy([
                            "id" => $id
                        ]);
                    }
                    $groupeCompetenceObj->addCompetence($skill);
                }
                if(!($groupeCompetence == $groupeCompetenceObj)){
                    $comptences = $groupeCompetence->getCompetences();
                    $groupeCompetence = $this->removeCompetence($groupeCompetence,$comptences);
                    $comptencesObj = $groupeCompetenceObj->getCompetences();
                    $groupeCompetence = $this->addCompetence($groupeCompetence,$comptencesObj);
                    $groupeCompetence->setLibelle($groupeCompetenceObj->getLibelle())
                                     ->setDescriptif($groupeCompetenceObj->getDescriptif());
//                    $manager->persist($groupeCompetence);
                    $manager->flush();
                }
                return $this->json($groupeCompetence,Response::HTTP_OK);
            }
        }
        return $this->json(["message" => "Ressource inexistante"],Response::HTTP_NOT_FOUND);
    }

    private function removeCompetence(GroupeCompetence $groupeCompetence,$competences)
    {
        foreach ($competences as $competence){
            $groupeCompetence->removeCompetence($competence);
        }
        return $groupeCompetence;
    }

    private function addCompetence(GroupeCompetence $groupeCompetence,$competences)
    {
        foreach ($competences as $competence){
            $groupeCompetence->addCompetence($competence);
        }
        return $groupeCompetence;
    }
}
