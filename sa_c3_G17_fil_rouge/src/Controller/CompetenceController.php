<?php

namespace App\Controller;

use App\Entity\Competence;
use App\Repository\CompetenceRepository;
use App\Repository\GroupeCompetenceRepository;
use App\Repository\NiveauRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CompetenceController extends AbstractController
{

    private $competenceRepository,
            $serializer;

    private const ACCESS_DENIED = "Vous n'avez pas accés à cette ressource.",
        RESOURCE_NOT_FOUND = "Ressource inexistante.",
        COMPETENCE_READ = "competence:read";

    public function __construct(CompetenceRepository $competenceRepository,SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
        $this->competenceRepository = $competenceRepository;
    }

    /**
     * @Route(
     *     path="/api/admins/competences/{id<\d+>}",
     *     methods={"GET"},
     *     name="getCompetence"
     * )
     */
    public function getCompetence($id)
    {
        if(!$this->isGranted("VIEW",new Competence()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $competence = $this->competenceRepository->findOneBy(["id" => $id]);
        if ($competence && !$competence->getIsDeleted()){
            $competence = $this->serializer->normalize($competence,null,["groups" => [self::COMPETENCE_READ]]);
            return $this->json($competence,Response::HTTP_OK);
        }
        return $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/admins/competences",
     *     methods={"GET"},
     *     name="getCompetences",
     *     defaults={
     *          "_api_receive"=false,
     *     }
     * )
     */
    public function getCompetences()
    {
        if(!($this->isGranted("VIEW",new Competence())))
        {
            return $this->json(["message" => self::ACCESS_DENIED,Response::HTTP_FORBIDDEN]);
        }
        $competences = $this->competenceRepository->findBy(["isDeleted" => false]);
        $competences = $this->serializer->normalize($competences,null,["groups" => [self::COMPETENCE_READ]]);
        return $this->json($competences,Response::HTTP_OK);
    }

    /**
     * @Route(
     *     path="/api/admins/competences",
     *     methods={"POST"},
     *     name="addCompetence"
     * )
     */
    public function addCompetence(TokenStorageInterface $tokenStorage,Request $request,EntityManagerInterface $manager,ValidatorInterface  $validator,GroupeCompetenceRepository $groupeCompetenceRepository)
    {
        if(!($this->isGranted("EDIT",new Competence())))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $competenceJson = $request->getContent();
        $administrateur = $tokenStorage->getToken()->getUser();
        $competenceTab = $this->serializer->decode($competenceJson,"json");
        $groupeCompetences = isset($competenceTab["groupeCompetences"]) ? $competenceTab["groupeCompetences"] : [];
        $niveaux = isset($competenceTab["niveaux"]) ? $competenceTab["niveaux"] : [];
        $competenceTab["groupeCompetences"] = [];
        $competenceTab["niveaux"] = [];
        $competenceObj = $this->serializer->denormalize($competenceTab,"App\Entity\Competence");
        $competenceObj->setIsDeleted(false);
        $errors = $validator->validate($competenceObj);
        if (count($errors)){
            return $this->json($errors,Response::HTTP_BAD_REQUEST);
        }
        if(!count($competenceTab)){
            return $this->json(["message" => "Ajouter au moins un groupe de competence"],Response::HTTP_BAD_REQUEST);
        }
        if(!count($niveaux) || count($niveaux) < 3){
            return $this->json(["message" => "Ajouter les 3 niveaux d'évaluation."],Response::HTTP_BAD_REQUEST);
        }
        $competenceObj = $this->addGroupeToCompetence($groupeCompetences,$groupeCompetenceRepository,$this->serializer,$administrateur,$validator,$competenceObj,$manager);
        foreach ($niveaux as $niveau)
        {
            $level = $this->serializer->denormalize($niveau,"App\Entity\Niveau");
            $level->setIsDeleted(false);
            $error = $validator->validate($level);
            if(count($error))
            {
                return $this->json($error,Response::HTTP_BAD_REQUEST);
            }
            $manager->persist($level);
            $competenceObj->addNiveau($level);
        }
        $manager->persist($competenceObj);
        $manager->flush();
        $competenceObj = $this->serializer->normalize($competenceObj,null,["groups" => [self::COMPETENCE_READ]]);
        return $this->json($competenceObj,Response::HTTP_CREATED);
    }
    
    /**
     * @Route(
     *     path="/api/admins/competences/{id<\d+>}",
     *     methods={"PUT"},
     *     name="setCompetence"
     * )
     */
    public function setCompetence($id,NiveauRepository $niveauRepository,TokenStorageInterface $tokenStorage,Request $request,EntityManagerInterface $manager,ValidatorInterface $validator,GroupeCompetenceRepository $groupeCompetenceRepository)
    {

        if(!($this->isGranted("SET",new Competence())))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $competence = $this->competenceRepository->findOneBy(["id" => $id]);
        if(!$competence || $competence->getIsDeleted())
        {
            return  $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
        }
        $competenceJson = $request->getContent();
        $competenceTab = $this->serializer->decode($competenceJson,"json");
        $groupeCompetence = isset($competenceTab["groupeCompetences"]) ? $competenceTab["groupeCompetences"] : [];
        $niveaux = isset($competenceTab["niveaux"]) ? $competenceTab["niveaux"] : [];
        $competenceTab["groupeCompetences"] = [];
        $competenceTab["niveaux"] = [];
        $competenceObj = $this->serializer->denormalize($competenceTab,"App\Entity\Competence");
        $errors = $validator->validate($competenceObj);
        if(count($errors))
        {
            return $this->json($errors,Response::HTTP_BAD_REQUEST);
        }
        if( count($niveaux) < 3)
        {
            return $this->json(["message" => "Les niveaux (3)  sont obligatoires."],Response::HTTP_BAD_REQUEST);
        }
        if(!count($groupeCompetence) || !isset($groupeCompetence[0]["id"]))
        {
            return $this->json(["message" => "Le groupe de competence est obligatoire."],Response::HTTP_BAD_REQUEST);
        }
        $idGrpeCompetence = (int) $groupeCompetence[0]["id"];
        $oldGroupeCompetenceObj = $groupeCompetenceRepository->findOneBy(["id" => $idGrpeCompetence]);
        if(!$oldGroupeCompetenceObj || $oldGroupeCompetenceObj->getIsDeleted())
        {
            return $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
        }
        $competenceObj->addGroupeCompetence($oldGroupeCompetenceObj);
        foreach ($niveaux as $niveau)
        {
            $level = $this->serializer->denormalize($niveau,"App\Entity\Niveau");
            $idLevel = isset($niveau["id"]) ? $niveau["id"] : null;
            $levels = $competence->getNiveaux()->getValues();
            if($idLevel)
            {
                $oldLevel = $niveauRepository->findOneBy(["id" => $idLevel]);
                if(!$oldLevel || $oldLevel->getIsDeleted())
                {
                    return $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
                }
                if(!in_array($oldLevel,$levels))
                {
                    return $this->json(["message" => "Cette niveau n'est pas dans cette competence."],Response::HTTP_BAD_REQUEST);
                }
                $error = $validator->validate($level);
                if (count($error))
                {
                    return $this->json($error,Response::HTTP_BAD_REQUEST);
                }
                $level->setId($idLevel);
                if($oldLevel != $level)
                {
                    $oldLevel->setLibelle($level->getLibelle())
                             ->setGroupeAction($level->getGroupeAction())
                             ->setCritereEvaluation($level->getCritereEvaluation());
                }
            }else{
                if (count($levels) > 3)
                {
                    return $this->json(["message" => "Une competence a au maximum 3 niveaux."],Response::HTTP_BAD_REQUEST);
                }
                $error = $validator->validate($levels);
                if (count($error))
                {
                    $this->json($error,Response::HTTP_BAD_REQUEST);
                }
                $level->setIsDeleted(false);
                $manager->persist($level);
                $competence->addNiveau($level);
            }
        }
        $competence->setLibelle($competenceObj->getLibelle())
                    ->setDescriptif($competenceObj->getDescriptif());
        $manager->flush();
        $competence = $this->serializer->normalize($competence,null,["groups" => [self::COMPETENCE_READ]]);
        return $this->json($competence,Response::HTTP_OK);
    }


    private function addGroupeToCompetence($groupeCompetences,$groupeCompetenceRepository,$serializer,$administrateur,$validator,$competenceObj,$manager)
    {
        foreach ($groupeCompetences as $groupeCompetence)
        {
            $id = isset($groupeCompetence["id"]) ? $groupeCompetence["id"] : null;
            if ($id)
            {
                $groupe = $groupeCompetenceRepository->findOneBy(["id" => $id]);
                if(!$groupe || $groupe->getIsDeleted())
                {
                    return $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
                }
                $competenceObj->addGroupeCompetence($groupe);
            }
        }
        return $competenceObj;
    }
}
