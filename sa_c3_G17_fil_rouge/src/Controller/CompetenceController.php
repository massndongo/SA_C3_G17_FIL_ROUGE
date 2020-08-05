<?php

namespace App\Controller;

use App\Entity\Competence;
use App\Repository\CompetenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CompetenceController extends AbstractController
{

    /**
     * @Route(
     *     path="/api/admin/competences/{id<\d+>}",
     *     methods={"GET"},
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
     * )
     */
    public function addCompetence(Request $request,EntityManagerInterface $manager,SerializerInterface $serializer,ValidatorInterface  $validator)
    {
        $competence = new Competence();
        if(!($this->isGranted("EDIT",$competence)))
            return $this->json(["message" => "Vous n'avez pas access à cette Ressource"],Response::HTTP_FORBIDDEN);
        $competence = $request->getContent();
        $competence = $serializer->deserialize($competence,"App\Entity\Competence","json");
        $competence->setIsDeleted(false);
        $errors = $validator->validate($competence);
        if (count($errors)){
            return $this->json($errors,Response::HTTP_BAD_REQUEST);
        }
        $groupe = $competence->getGroupeCompetence();
        if(!count($groupe)){
            return $this->json(["message" => "Ajouter au moins un groupe de competence"],Response::HTTP_BAD_REQUEST);
        }
        $manager->persist($competence);
        $manager->flush();
        return $this->json($competence,Response::HTTP_CREATED);
    }

    /**
     * @Route(
     *     path="/api/admin/competences/{id<\d+>}",
     *     methods={"GET"},
     * )
     */
    public function setCompetence($id,CompetenceRepository $competenceRepository)
    {
        $competence = new Competence();
        if(!($this->isGranted("SET",$competence)))
            return $this->json(["message" => "Vous n'avez pas access à cette Ressource"],Response::HTTP_FORBIDDEN);
        $competence = $competenceRepository->findOneBy([
            "id" => $id
        ]);
        if($competence){
            if(!$competence->getIsDeleted())
                return $this->json(["message" => "Desolé cette fonctionnalité est en cours de réalisation. Revenez plus tard :)"],Response::HTTP_OK);
        }
        return $this->json(["message" => "Ressource inexistante"],Response::HTTP_NOT_FOUND);
    }
}
