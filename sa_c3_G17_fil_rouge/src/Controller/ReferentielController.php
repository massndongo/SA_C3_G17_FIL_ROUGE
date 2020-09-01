<?php

namespace App\Controller;

use App\Entity\Referentiel;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReferentielRepository;
use App\Repository\GroupeCompetenceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ReferentielController extends AbstractController
{
    private $serializer,
            $referentielRepository;

    private const ACCESS_DENIED = "Vous n'avez pas accés à cette ressource.",
        RESOURCE_NOT_FOUND = "Ressource inexistante.",
        REFERENTIEL_READ = "referentiel:read",
        REFERENTIEL_GROUPE = "refGroupe:read";

    public function __construct (SerializerInterface $serializer,ReferentielRepository $referentielRepository)
    {
        $this->serializer = $serializer;
        $this->referentielRepository = $referentielRepository;
    }
    /**
     * @Route(
     *     path="/api/admins/referentiels",
     *     methods={"GET"},
     *     name="getReferentiels"
     * )
     */
    public function getReferentiels()
    {
        if(!($this->isGranted("VIEW",new Referentiel())))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $referentiels = $this->referentielRepository->findBy(["isDeleted" => false]);
        $referentiels = $this->serializer->normalize($referentiels,null,["groups" => [self::REFERENTIEL_READ]]);
        return $this->json($referentiels,Response::HTTP_OK);
    }

    /**
     * @Route(
     *     path="/api/admins/referentiels/grpecompetences",
     *     methods={"GET"},
     *     name="get_grpecompetences"
     * )
     */
    public function getGroupeCompetence()
    {
        if(!($this->isGranted("VIEW",new Referentiel())))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $referentiels = $this->referentielRepository->findBy(["isDeleted" => false]);
        $referentiels = $this->serializer->normalize($referentiels,null,["groups" => [self::REFERENTIEL_READ,self::REFERENTIEL_GROUPE]]);
        return $this->json($referentiels,Response::HTTP_OK);
    }

//     /**
//     * @Route(
//     *     path="/api/admins/referentiels",
//     *     methods={"POST"},
//     *     name="addReferentiel"
//     * )
//     */
//    public function addReferentiel(GroupeCompetenceRepository $grpeCompetenceRepository,TokenStorageInterface $tokenStorage,Request $request,EntityManagerInterface $manager,ValidatorInterface $validator)
//    {
//
//        if(!($this->isGranted("EDIT",new Referentiel())))
//        {
//            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
//        }
//        $referentielJson= $request->getContent();
//        $referentielTab = $this->serializer->decode($referentielJson,"json");
//        $grpeCompetences = isset($referentielTab["groupeCompetence"]) ? $referentielTab["groupeCompetence"] : [];
//        $referentielTab["groupeCompetence"] = [];
//        $referentielObj = $this->serializer->denormalize($referentielTab, "App\Entity\Referentiel");
//        $errors = $validator->validate($referentielObj);
//        if(count($errors))
//        {
//            return $this->json($errors,Response::HTTP_BAD_REQUEST);
//        }
//
//        if (!count($grpeCompetences)) {
//            return $this->json(["message" => "Ce groupe de competences n'existe pas."],Response::HTTP_BAD_REQUEST);
//        }
//        $referentielObj = $this->addgrpeComptenceToRef($grpeCompetences,$this->serializer,$validator,$referentielObj,$manager,$grpeCompetenceRepository);
//        if (!count($grpeCompetences))
//        {
//            return $this->json(["message" => "Ajoutez au moins un groupe de competences existant à cet referentiel."],Response::HTTP_BAD_REQUEST);
//        }
//        $manager->persist($referentielObj);
//        $manager->flush();
//        $referentielObj = $this->serializer->normalize($referentielObj,null,["groups" => [self::REFERENTIEL_GROUPE]]);
//        return $this->json($referentielObj,Response::HTTP_CREATED);
//    }

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
     *     path="/api/admins/referentiels/{id<\d+>}",
     *     methods={"GET"},
     *     name="getReferentiel"
     * )
     */
    public function getReferentiel($id)
    {
        if(!($this->isGranted("VIEW",new Referentiel())))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $referentiel = $this->referentielRepository->findOneBy(["id" => $id]);
        if($referentiel && !$referentiel->getIsDeleted()){
            $referentiel = $this->serializer->normalize($referentiel,null,["groups" => [self::REFERENTIEL_READ]]);
            return $this->json($referentiel,Response::HTTP_OK);
        }
        return $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }
     /**
     * @Route(
     *     path="/api/admins/referentiels/{id<\d+>}/grpecompetences",
     *     methods={"GET"},
     *     name="getGroupeCompetencesInReferentiel"
     * )
     */
    public function getGroupeCompetencesInReferentiel($id)
    {
        if(!($this->isGranted("VIEW",new Referentiel())))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $referentiel = $this->referentielRepository->findOneBy(["id" => $id]);
        if($referentiel && !$referentiel->getIsDeleted()){
            $referentiel = $this->serializer->normalize($referentiel,null,["groups" => [self::REFERENTIEL_READ]]);
            return $this->json($referentiel,Response::HTTP_OK);
        }
        return $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }

//        /**
//     * @Route(
//     *     path="/api/admins/referentiels/{id}",
//     *     methods={"PUT"},
//     *     name="set_referentiel"
//     * )
//     */
//    public function setReferentiel($id,EntityManagerInterface $manager,GroupeCompetenceRepository $groupeCompetenceRepository,Request $request,ValidatorInterface $validator)
//    {
//        if(!$this->isGranted("EDIT",new Referentiel()))
//        {
//            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
//        }
//        $referentielJson = $request->getContent();
//        $referentielTab = $this->serializer->decode($referentielJson,"json");
//        $grpeCompetences = isset($referentielTab["groupeCompetence"]) ? $referentielTab["groupeCompetence"] : [];
//        $referentielTab["groupeCompetence"] = [];
//        $referentielObj = $this->serializer->denormalize($referentielTab,"App\Entity\Referentiel");
//        $referentiel = $this->referentielRepository->findOneBy(["id" => $id]);
//        $referentielObj->setId((int)$id)
//            ->SetIsDeleted(false);
//        if($referentiel && !$referentiel->getIsDeleted())
//        {
//            $referentielObj = $this->addgrpeComptenceToRef($grpeCompetences,$this->serializer,$validator,$referentielObj,$manager,$groupeCompetenceRepository);
//            if($referentiel != $referentielObj){
//                $grpeCompetences = $referentiel->getGroupeCompetence();
//                $referentiel = $this->removeGroupeCompetence($referentiel,$grpeCompetences);
//                $groupeComptencesObj = $referentielObj->getGroupeCompetence();
//                $referentiel = $this->addGroupeCompetence($referentiel,$groupeComptencesObj);
//                $referentiel->setLibelle($referentielObj->getLibelle())
//                    ->setPresentation($referentielObj->getPresentation());
//                $manager->flush();
//            }
//            $referentiel = $this->serializer->normalize($referentiel,null,["groups" => [self::REFERENTIEL_READ]]);
//            return $this->json($referentiel,Response::HTTP_OK);
//        }
//        return $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
//    }
//    private function removeGroupeCompetence(Referentiel $referentiel,$grpeCompetences)
//    {
//        foreach ($grpeCompetences as $grpeCompetence){
//            $referentiel->removeGroupeCompetence($grpeCompetence);
//        }
//        return $referentiel;
//    }

        /**
     * @Route(
     *     path="/api/admins/referentiels/{id<\d+>}",
     *     methods={"DELETE"},
     *     name="delGroupeCompetence"
     * )
     */
    public function delReferentiel($id,EntityManagerInterface $manager,ReferentielRepository $referentielRepository)
    {
        if(!$this->isGranted("DEL",new Referentiel()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $referentiel = $referentielRepository->findOneBy(["id" => $id]);
        if ($referentiel && !$referentiel->getIsDeleted()){
            $referentiel->setIsDeleted(true);
            $manager->flush();
            $referentiel = $this->serializer->normalize($referentiel,null,["groups" => [self::REFERENTIEL_READ]]);
            return $this->json($referentiel,Response::HTTP_OK);
        }
        return $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }

}
