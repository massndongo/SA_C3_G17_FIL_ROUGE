<?php

namespace App\Controller;

use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Repository\AdminRepository;
use App\Repository\GroupeCompetenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class GroupeCompetenceController extends AbstractController
{
    /**
     * @Route(
     *     path="admin/grpecompetences",
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
        $groupeCompetences = $groupeCompetenceRepository->findBy([
            "isDeleted" => false
        ]);
        return $this->json($groupeCompetences,Response::HTTP_OK);
    }

    /**
     * @Route(
     *     path="admin/grpecompetences/{id<\d+>}/competences",
     *     methods={"GET"},
     *     defaults={
     *          "__controller"="App\Controller\GroupeCompetenceController::getCompetencesInGroupeCompetence",
     *          "__api_resource_class"=GroupeCompetence::class,
     *          "__api_item_operation_name"="get_competence_in_grpeCompetence"
     *     }
     * )
     */
    public function getCompetencesInGroupeCompetence($id,GroupeCompetenceRepository $groupeCompetenceRepository)
    {
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
     *     path="admin/grpecompetences",
     *     methods={"POST"},
     *     defaults={
     *          "__controller"="App\Controller\GroupeCompetenceController::addGroupeCompetence",
     *          "__api_resource_class"=GroupeCompetence::class,
     *          "__api_collection_operation_name"="add_groupeCompetence"
     *     }
     * )
     */
    public function addGroupeCompetence(Request $request,EntityManagerInterface $manager,SerializerInterface $serializer,ValidatorInterface $validator,AdminRepository $adminRepository)
    {
        $groupeCompetence = $request->request->all();
        $groupeCompetence = $serializer->denormalize($groupeCompetence,"App\Entity\GroupeCompetence");
        $groupeCompetence->setIsDeleted(false);
        $token = substr($request->server->get("HTTP_AUTHORIZATION"),7);
        $token = explode(".",$token);
        $payload = $token[1];
        $payload = json_decode(base64_decode($payload));
        $admin = $adminRepository->findOneBy([
            "username"=> $payload->username
        ]);
        $groupeCompetence->setAdministrateur($admin);
        /*dd($groupeCompetence);*/
        $errors = (array)$validator->validate($groupeCompetence);
        if(count($errors)){
            return $this->json($errors,Response::HTTP_BAD_REQUEST);
        }
        if (!count($groupeCompetence->getCompetences()))
            return $this->json(["message" => "Ajoutez au moins une competence à cet groupe de competence."],Response::HTTP_BAD_REQUEST);
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
