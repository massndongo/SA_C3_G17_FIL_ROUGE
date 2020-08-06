<?php

namespace App\Controller;

use App\Entity\Competence;
use App\Repository\CompetenceRepository;
use App\Repository\GroupeCompetenceRepository;
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

    /**
     * @Route(
     *     path="/api/admin/competences/{id<\d+>}",
     *     methods={"GET"},
     *     name="getCompetence"
     * )
     */
    public function getCompetence($id,CompetenceRepository $competenceRepository)
    {
        $competence = new Competence();
        if(!$this->isGranted("VIEW",$competence))
            return $this->json(["message" => "Vous n'avez pas access à cette Ressource"],Response::HTTP_FORBIDDEN);
        $competence = $competenceRepository->findOneBy([
            "id" => $id
        ]);
        if ($competence){
            if (!$competence->getIsDeleted())
                return $this->json($competence,Response::HTTP_OK);
        }
        return $this->json(["message" => "Ressource inexistante"],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/admin/competences",
     *     methods={"GET"},
     *     name="getCompetences"
     * )
     */
    public function getCompetences(CompetenceRepository $competenceRepository)
    {
        if(!($this->isGranted("ROLE_FORMATEUR")))
            return $this->json(["message" => "Vous n'avez pas access à cette Ressource"],Response::HTTP_FORBIDDEN);
        $competences = $competenceRepository->findBy([
            "isDeleted" => false
        ]);
        return $this->json($competences,Response::HTTP_OK);
    }

    /**
     * @Route(
     *     path="/api/admin/competences",
     *     methods={"POST"},
     *     name="addCompetence"
     * )
     */
    public function addCompetence(TokenStorageInterface $tokenStorage,Request $request,EntityManagerInterface $manager,SerializerInterface $serializer,ValidatorInterface  $validator,GroupeCompetenceRepository $groupeCompetenceRepository)
    {
        $competence = new Competence();
        if(!($this->isGranted("EDIT",$competence)))
            return $this->json(["message" => "Vous n'avez pas access à cette Ressource"],Response::HTTP_FORBIDDEN);
        $competenceJson = $request->getContent();
        $administrateur = $tokenStorage->getToken()->getUser();
        $competenceTab = $serializer->decode($competenceJson,"json");
        $groupeCompetences = isset($competenceTab["groupeCompetences"]) ? $competenceTab["groupeCompetences"] : [];
        $niveaux = isset($competenceTab["niveaux"]) ? $competenceTab["niveaux"] : [];
        $competenceTab["groupeCompetences"] = [];
        $competenceTab["niveaux"] = [];
        $competenceObj = $serializer->denormalize($competenceTab,"App\Entity\Competence");
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
        $competenceObj = $this->addGroupeToCompetence($groupeCompetences,$groupeCompetenceRepository,$serializer,$administrateur,$validator,$competenceObj,$manager);
        foreach ($niveaux as $niveau)
        {
            $level = $serializer->denormalize($niveau,"App\Entity\Niveau");
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
        return $this->json($competenceObj,Response::HTTP_CREATED);
    }
    
    /**
     * @Route(
     *     path="/api/admin/competences/{id<\d+>}",
     *     methods={"PUT"},
     *     name="setCompetence"
     * )
     */
    public function setCompetence($id,CompetenceRepository $competenceRepository,TokenStorageInterface $tokenStorage,Request $request,SerializerInterface $serializer,EntityManagerInterface $manager,ValidatorInterface $validator,GroupeCompetenceRepository $groupeCompetenceRepository)
    {
        $competence = new Competence();
        if(!($this->isGranted("SET",$competence)))
            return $this->json(["message" => "Vous n'avez pas access à cette Ressource"],Response::HTTP_FORBIDDEN);

    }

    private function remoGroupeFromCompetence($competenceObj)
    {
        $groupes = $competenceObj->getGroupeCompetence();
        foreach ($groupes as $groupe){
            $competenceObj->removeGroupeCompetence($groupe);
        }
        return $competenceObj;
    }

    private function addGroupeToCompetence($groupeCompetences,$groupeCompetenceRepository,$serializer,$administrateur,$validator,$competenceObj,$manager)
    {
        foreach ($groupeCompetences as $groupeCompetence)
        {
            $id = isset($groupeCompetence["id"]) ? $groupeCompetence["id"] : null;
            if ($id)
            {
                $groupe = $groupeCompetenceRepository->findOneBy([
                    "id" => $id
                ]);
                if(!$groupe || $groupe->getIsDeleted())
                    return $this->json(["message" => "Ressource inexistante"],Response::HTTP_NOT_FOUND);
                $competenceObj->addGroupeCompetence($groupe);
            }
        }
        return $competenceObj;
    }
}
