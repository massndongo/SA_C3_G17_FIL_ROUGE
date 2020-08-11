<?php

namespace App\Controller;

use App\Entity\Referentiel;
use App\Entity\GroupeCompetence;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReferentielRepository;
use App\Repository\GroupeCompetenceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use ApiPlatform\Core\Validator\ValidatorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ReferentielController extends AbstractController
{
    /**
     * @Route(
     *     path="/api/admin/referentiels",
     *     methods={"GET"},
     *     name="getReferentiels"
     * )
     */
    public function getReferentiels(ReferentielRepository $referentielRepository)
    {
        if(!($this->isGranted("ROLE_FORMATEUR")))
            return $this->json(["message" => "Vous n'avez pas access à cette Ressource"],Response::HTTP_FORBIDDEN);
        $referentiel = $referentielRepository->findBy([
            "isDeleted" => false
        ]);
        return $this->json($referentiel,Response::HTTP_OK);
    }

    /**
     * @Route(
     *     path="/api/admin/referentiels/grpecompetences",
     *     methods={"GET"},
     *     name="get_grpecompetences"
     * )
     */
    public function getGroupeCompetence(ReferentielRepository $referentielRepository)
    {
        $referentiel= new Referentiel();
        if(!($this->isGranted("VIEW",$referentiel)))
            return $this->json(["message" => "Vous n'avez pas access à cette Ressource"],Response::HTTP_FORBIDDEN);
        $referentiel = $referentielRepository->findBy([
            "isDeleted" => false
        ]);
        $groupeCompetences = [];
        $size = count($referentiel);
        for ($i = 0;$i < $size; $i++){
            if(!$referentiel[$i]->getIsDeleted()){
                $groupeCompetence = $referentiel[$i]->getGroupeCompetence();
                $length = count($groupeCompetence);
                for ($j = 0; $j < $length; $j++){
                    $skill = $groupeCompetence[$j];
                    if(!$skill->getIsDeleted()){
                        $groupeCompetences[] = $skill;
                    }
                }
            }
        }
        return $this->json($groupeCompetences,Response::HTTP_OK);
    }

     /**
     * @Route(
     *     path="/api/admin/referentiels",
     *     methods={"POST"},
     *     name="addReferentiel"
     * )
     */
    public function addReferentiel(GroupeCompetenceRepository $grpeCompetenceRepository,TokenStorageInterface $tokenStorage,Request $request,EntityManagerInterface $manager,SerializerInterface $serializer,ValidatorInterface $validator)
    {
        $referentiel = new Referentiel();
        if(!($this->isGranted("EDIT",$referentiel)))
            return $this->json(["message" => "Vous n'avez pas access à cette Ressource"],Response::HTTP_FORBIDDEN);
        $referentielTab = $request->request->all();
        $programme = $request->files->get("programme");
        $programme = fopen($programme->getRealPath(),"rb"); 
        $referentielTab["programme"] = $programme;
        $grpeCompetences = $referentielTab["groupeCompetence"];
        $referentielTab["groupeCompetence"] = [];
        $referentielObj = $serializer->denormalize($referentielTab, "App\Entity\Referentiel");
        $referentielObj->setLibelle($referentielTab["libelle"]);
        $referentielObj->setProgramme($referentielTab["programme"]);
        $referentielObj->setPresentation($referentielTab["presentation"]);
        $referentielObj->setCritereEvaluation($referentielTab["critereEvaluation"]);
        $referentielObj->setCritereAdmission($referentielTab["critereAdmission"]);
        $referentielObj->setIsDeleted(false);
        if (!$this->addgrpeComptenceToRef($grpeCompetences,$serializer,$validator,$referentielObj,$manager,$grpeCompetenceRepository)) {
            return $this->json(["message" => "Ce groupe de competences n'existe pas."],Response::HTTP_BAD_REQUEST);
        }
        $referentielObj = $this->addgrpeComptenceToRef($grpeCompetences,$serializer,$validator,$referentielObj,$manager,$grpeCompetenceRepository);
        $errors = (array)$validator->validate($referentielObj);
        if(count($errors))
            return $this->json($errors,Response::HTTP_BAD_REQUEST);
        if (!count($grpeCompetences))
            return $this->json(["message" => "Ajoutez au moins un groupe de competences existant à cet referentiel."],Response::HTTP_BAD_REQUEST);
        $manager->persist($referentielObj);
        $manager->flush();
        fclose($programme);
        return $this->json($referentielObj,Response::HTTP_CREATED);
    }

    private function addGroupeCompetence(Referentiel $referentiel,$grpeCompetences)
    {
        foreach ($grpeCompetences as $groupe){
            $referentiel->addGroupeCompetence($groupe);
        }
        return $referentiel;
    }

    private function addgrpeComptenceToRef($grpeCompetences,$serializer,$validator,$referentielObj,$manager,$grpeCompetenceRepository)
    {
        foreach ($grpeCompetences as $groupe){
            $id = isset($groupe) ? $groupe : null;
            $skill = $serializer->denormalize($grpeCompetences,"App\Entity\GroupeCompetence");
            $skill->setId((int)$id);
            $skill->setIsDeleted(false)
                ->addReferentiel($referentielObj);
            $error = (array) $validator->validate($skill);
            if (count($error))
                return $this->json($error,Response::HTTP_BAD_REQUEST);
            if($id == null || !$grpeCompetenceRepository->findById($id)){
                return $skill = null;
            }else{
                $skill = $grpeCompetenceRepository->findOneBy([
                    "id" => $id
                ]);
            }
            $referentielObj->addGroupeCompetence($skill);
        }
        return $referentielObj;
    }

     /**
     * @Route(
     *     path="/api/admin/referentiels/{id<\d+>}",
     *     methods={"GET"},
     *     name="getReferentiel"
     * )
     */
    public function getReferentiel($id,ReferentielRepository $referentielRepository)
    {
        $referentiel = new Referentiel();
        if(!($this->isGranted("VIEW",$referentiel)))
            return $this->json(["message" => "Vous n'avez pas access à cette Ressource"],Response::HTTP_FORBIDDEN);
        $referentiel = $referentielRepository->findOneBy([
            "id" => $id
        ]);
        if($referentiel){
            if (!$referentiel->getIsDeleted())
                return $this->json($referentiel,Response::HTTP_OK);
        }
        return $this->json(["message" => "Ressource inexistante"],Response::HTTP_NOT_FOUND);
    }
     /**
     * @Route(
     *     path="/api/admin/referentiels/{id<\d+>}/grpecompetences",
     *     methods={"GET"},
     *     name="getGroupeCompetencesInReferentiel"
     * )
     */
    public function getGroupeCompetencesInReferentiel($id,ReferentielRepository $referentielRepository)
    {
        $referentiel = new Referentiel();
        if(!($this->isGranted("VIEW",$referentiel)))
            return $this->json(["message" => "Vous n'avez pas access à cette Ressource"],Response::HTTP_FORBIDDEN);
        $referentiel = $referentielRepository->findOneBy([
            "id" => $id
        ]);
        if($referentiel){
            if (!$referentiel->getIsDeleted()){
                $groupeCompetences = $referentiel->getGroupeCompetence();
                return $this->json($groupeCompetences,Response::HTTP_OK);
            }
        }
        return $this->json(["message" => "Ressource inexistante"],Response::HTTP_NOT_FOUND);
    }

        /**
     * @Route(
     *     path="/api/admin/referentiels/{id}",
     *     methods={"PUT"},
     *     name="set_referentiel"
     * )
     */
    public function setReferentiel($id,EntityManagerInterface $manager,ReferentielRepository $referentielRepository,GroupeCompetenceRepository $groupeCompetenceRepository,Request $request,SerializerInterface $serializer,ValidatorInterface $validator)
    {
        $referentiel = new Referentiel();
        if(!$this->isGranted("EDIT",$referentiel))
            return $this->json(["message" => "Vous n'avez pas access à cette Ressource"],Response::HTTP_FORBIDDEN);
        $referentielTab = $request->request->all();
        $grpeCompetences = $referentielTab["groupeCompetence"];
        $referentielTab["groupeCompetence"] = [];
        $referentielObj = $serializer->denormalize($referentielTab,"App\Entity\Referentiel");
        $referentiel = $referentielRepository->findOneBy([
            "id" => $id
        ]);
        $referentielObj->setId((int)$id)
            ->SetIsDeleted(false);
        if($referentiel)
        {
            if(!$referentiel->getIsDeleted())
            {
                $referentielObj = $this->addgrpeComptenceToRef($grpeCompetences,$serializer,$validator,$referentielObj,$manager,$groupeCompetenceRepository);
                if($referentiel != $referentielObj){
                    $grpeCompetences = $referentiel->getGroupeCompetence();
                    $referentiel = $this->removeGroupeCompetence($referentiel,$grpeCompetences);
                    $groupeComptencesObj = $referentielObj->getGroupeCompetence();
                    $referentiel = $this->addGroupeCompetence($referentiel,$groupeComptencesObj);
                    $referentiel->setLibelle($referentielObj->getLibelle())
                                     ->setPresentation($referentielObj->getPresentation());
                    $manager->flush();
                }
                return $this->json($referentiel,Response::HTTP_OK);
            }
        }
        return $this->json(["message" => "Ressource inexistante"],Response::HTTP_NOT_FOUND);
    }
    private function removeGroupeCompetence(Referentiel $referentiel,$grpeCompetences)
    {
        foreach ($grpeCompetences as $grpeCompetence){
            $referentiel->removeGroupeCompetence($grpeCompetence);
        }
        return $referentiel;
    }

        /**
     * @Route(
     *     path="/api/admin/referentiels/{id<\d+>}",
     *     methods={"DELETE"},
     *     name="delGroupeCompetence"
     * )
     */
    public function delReferentiel($id,EntityManagerInterface $manager,ReferentielRepository $referentielRepository)
    {
        $referentiel = new Referentiel();
        if(!$this->isGranted("DEL",$referentiel))
            return $this->json(["message" => "Vous ne pouvez pas supprimer cette Ressource"],Response::HTTP_FORBIDDEN);
        $referentiel = $referentielRepository->findOneBy([
            "id" => $id
        ]);
        if ($referentiel){
            if(!$referentiel->getIsDeleted()){
                $referentiel->setIsDeleted(true);
                $manager->flush();
                return $this->json($referentiel,Response::HTTP_OK);
            }
        }
        return $this->json(["message" => "Ressource inexistante"],Response::HTTP_NOT_FOUND);
    }

}
