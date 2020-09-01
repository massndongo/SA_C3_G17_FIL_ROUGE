<?php

namespace App\Controller;

use ApiPlatform\Core\Validator\ValidatorInterface;
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
    private $groupeCompetenceRepository,
            $serializer;
    private const ACCESS_DENIED = "Vous n'avez pas access à cette Ressource",
                RESOURCE_NOT_FOUND = "Ressource inexistante",
                GROUPE_COMPETENCE_READ = "grpecompetence:read_m",
                COMPETENCE_READ = "grpecompetence:competence:read";

    public function __construct(GroupeCompetenceRepository $groupeCompetenceRepository,SerializerInterface $serializer)
    {
        $this->groupeCompetenceRepository = $groupeCompetenceRepository;
        $this->serializer = $serializer;
    }

    /**
     * @Route(
     *     path="/api/admins/grpecompetences",
     *     methods={"GET"},
     *     name="getGroupeCompetences"
     * )
     */
    public function getGroupeCompetences()
    {
        if(!($this->isGranted("VIEW",new GroupeCompetence())))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $groupeCompetences = $this->groupeCompetenceRepository->findBy(["isDeleted" => false]);
        $groupeCompetences = $this->serializer->normalize($groupeCompetences,null,["groups" => [self::GROUPE_COMPETENCE_READ]]);
        return $this->json($groupeCompetences,Response::HTTP_OK);
    }

    /**
     * @Route(
     *     path="/api/admins/grpecompetences/{id<\d+>}/competences",
     *     methods={"GET"},
     *     name="getCompetencesInGroupeCompetence"
     * )
     */
    public function getCompetencesInGroupeCompetence($id)
    {
        if(!($this->isGranted("VIEW",new GroupeCompetence())))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $groupeCompetence = $this->groupeCompetenceRepository->findOneBy(["id" => $id]);
        if($groupeCompetence && !$groupeCompetence->getIsDeleted())
        {
            $groupeCompetence = $this->serializer->normalize($groupeCompetence,null,["groups" => [self::COMPETENCE_READ]]);
            return $this->json($groupeCompetence,Response::HTTP_OK);
        }
        return $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/admins/grpecompetences/competences",
     *     methods={"GET"},
     *     name="getCompetencesInGroupeCompetences"
     * )
     */
    public function getCompetencesInGroupeCompetences()
    {
        if(!($this->isGranted("VIEW",new GroupeCompetence())))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $groupeCompetences = $this->groupeCompetenceRepository->findBy(["isDeleted" => false]);
        $groupeCompetences = $this->serializer->normalize($groupeCompetences,null,["groups" => [self::COMPETENCE_READ]]);
        return $this->json($groupeCompetences,Response::HTTP_OK);
    }
    
    /**
     * @Route(
     *     path="/api/admins/grpecompetences",
     *     methods={"POST"},
     *     name="addGroupeCompetence"
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
        $competences = $groupeCompetenceTab["competences"];
        $groupeCompetenceTab["competences"] = [];
        $groupeCompetenceObj = $serializer->denormalize($groupeCompetenceTab,"App\Entity\GroupeCompetence");
        $groupeCompetenceObj->setIsDeleted(false)
            ->setAdministrateur($administrateur);
        $groupeCompetenceObj = $this->addComptenceToGroupe($competences,$serializer,$validator,$groupeCompetenceObj,$manager,$competenceRepository);
        $errors = (array)$validator->validate($groupeCompetenceObj);
        if(count($errors))
            return $this->json($errors,Response::HTTP_BAD_REQUEST);
        if (!count($competences))
            return $this->json(["message" => "Ajoutez au moins une competence à cet groupe de competence."],Response::HTTP_BAD_REQUEST);
        $manager->persist($groupeCompetenceObj);
        $manager->flush();
        return $this->json($groupeCompetenceObj,Response::HTTP_CREATED);
    }

    /**
     * @Route(
     *     path="/api/admins/grpecompetences/{id<\d+>}",
     *     methods={"GET"},
     *     name="getGroupeCompetence"
     * )
     */
    public function getGroupeCompetence($id)
    {
        if(!($this->isGranted("VIEW",new GroupeCompetence())))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $groupeCompetence = $this->groupeCompetenceRepository->findOneBy(["id" => $id]);
        if($groupeCompetence && !$groupeCompetence->getIsDeleted()){
            $groupeCompetence = $this->serializer->normalize($groupeCompetence,null,["groups" => [self::GROUPE_COMPETENCE_READ]]);
            return $this->json($groupeCompetence,Response::HTTP_OK);
        }
        return $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/admins/grpecompetences/{id<\d+>}",
     *     methods={"PUT"},
     *     name="setGroupeCompetence"
     * )
     */
    public function setGroupeCompetence($id,EntityManagerInterface $manager,CompetenceRepository $competenceRepository,Request $request,ValidatorInterface $validator)
    {
        if(!$this->isGranted("EDIT",new GroupeCompetence()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $groupeCompetenceJson = $request->getContent();
        $groupeCompetenceTab = $this->serializer->decode($groupeCompetenceJson,"json");
        $competences = isset($groupeCompetenceTab["competences"]) ? $groupeCompetenceTab["competences"] : [];
        $groupeCompetenceTab["competences"] = [];
        $groupeCompetenceObj = $this->serializer->denormalize($groupeCompetenceTab,"App\Entity\GroupeCompetence");
        $groupeCompetence = $this->groupeCompetenceRepository->findOneBy(["id" => $id]);
        $groupeCompetenceObj->setId((int)$id)
                            ->setAdministrateur($groupeCompetence->getAdministrateur())
                            ->SetIsDeleted(false);
        if($groupeCompetence && !$groupeCompetence->getIsDeleted())
        {
            $groupeCompetenceObj = $this->addComptenceToGroupe($competences,$this->serializer,$validator,$groupeCompetenceObj,$manager,$competenceRepository);
            if($groupeCompetence != $groupeCompetenceObj){
                $comptences = $groupeCompetence->getCompetences();
                $groupeCompetence = $this->removeCompetence($groupeCompetence,$comptences);
                $comptencesObj = $groupeCompetenceObj->getCompetences();
                $groupeCompetence = $this->addCompetence($groupeCompetence,$comptencesObj);
                $groupeCompetence->setLibelle($groupeCompetenceObj->getLibelle())
                                ->setDescriptif($groupeCompetenceObj->getDescriptif());
                $manager->flush();
            }
            $groupeCompetence = $this->serializer->normalize($groupeCompetence,null,["groups" => [self::GROUPE_COMPETENCE_READ,self::COMPETENCE_READ]]);
            return $this->json($groupeCompetence,Response::HTTP_OK);

        }
        return $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }

//    /**
//     * @Route(
//     *     path="/api/admins/grpecompetences/{id<\d+>}",
//     *     methods={"DELETE"},
//     *     name="delGroupeCompetence",
//     *     defaults={
//     *          "_api_resource_class"=GroupeCompetence::class,
//     *          "_api_collection_operation_name"="delGroupeCompetence",
//     *          "_controller"="App\Controller\BriefController::delGroupeCompetence",
//     *          "_api_receive"=false,
//     *     }
//     * )
//     */
//    public function delGroupeCompetence($id,EntityManagerInterface $manager)
//    {
//        dd("hi");
//        if(!$this->isGranted("DELETE",new GroupeCompetence()))
//        {
//            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
//        }
//        $groupeCompetence = $this->groupeCompetenceRepository->findOneBy(["id" => $id]);
//        if ($groupeCompetence && !$groupeCompetence->getIsDeleted()){
//            $groupeCompetence->setIsDeleted(true);
//            $manager->flush();
//            $groupeCompetence = $this->serializer->normalize($groupeCompetence,null,["groups" => [self::GROUPE_COMPETENCE_READ]]);
//            return $this->json($groupeCompetence,Response::HTTP_OK);
//        }
//        return $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
//    }
    
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

    private function addComptenceToGroupe($competences,$serializer,$validator,$groupeCompetenceObj,$manager,$competenceRepository)
    {
        foreach ($competences as $comptence){
            $comptence["niveaux"] = [];
            $skill = $serializer->denormalize($comptence,"App\Entity\Competence");
            $id = isset($comptence["id"]) ? (int)$comptence["id"] : null;
            if($id)
            {
                $skill = $competenceRepository->findOneBy(["id" => $id]);
                if(!$skill)
                    return $this->json(["message" => "La competence avec l'id : $id, n'existe pas."],Response::HTTP_NOT_FOUND);
                $groupeCompetenceObj->addCompetence($skill);
            }else{
                $skill->setIsDeleted(false);
                $error = (array) $validator->validate($skill);
                if (count($error))
                    return $this->json($error,Response::HTTP_BAD_REQUEST);
                $manager->persist($skill);
                $groupeCompetenceObj->addCompetence($skill);
            }
        }
        return $groupeCompetenceObj;
    }
}
