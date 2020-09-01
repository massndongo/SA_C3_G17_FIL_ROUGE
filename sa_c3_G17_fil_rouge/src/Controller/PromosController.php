<?php

namespace App\Controller;

use App\Entity\Promos;
use App\Entity\StatistiquesCompetences;
use App\Repository\ApprenantRepository;
use App\Repository\FormateurRepository;
use App\Repository\GroupeCompetenceRepository;
use App\Repository\PromosRepository;
use App\Repository\ReferentielRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PromosController extends AbstractController
{
    private $manager,
            $serializer,
            $validator,
            $promosRepository;
    private const PROMO_READ = "promos:read",
                ACCESS_DENIED = "Vous n'avez pas access à cette Ressource",
                RESOURCE_NOT_FOUND = "Ressource inexistante",
                PROMO_APPRENANT_READ = "promos:appreant:read";
    public function __construct(ValidatorInterface $validator,EntityManagerInterface $manager,SerializerInterface $serializer,PromosRepository $promosRepository)
    {
        $this->serializer = $serializer;
        $this->promosRepository = $promosRepository;
        $this->manager = $manager;
        $this->validator = $validator;
    }


//    /**
//     * @Route(
//     *     path="/mail",
//     *     name="mail"
//     * )
//     */
//    public function email(\Swift_Mailer $mailer,GroupeCompetenceRepository $repository)
//    {
//
//        $sender = 'terangawebdevelopment@gmail.com';
//        $receiver = "massndongo86@gmail.com";
//        $message = (new \Swift_Message("Ajout apprenant au promo"))
//            ->setFrom($sender)
//            ->setTo($receiver)
//            ->setBody(
//                $this->renderView(
//                    "emails/congratulation.html.twig",["nothing"]
//                ),
//                "text/html"
//            );
//        $mailer->send($message);
//        $grpeCompetence = $repository->findOneBy(["id" => 1]);
//        return $this->json($grpeCompetence,Response::HTTP_OK);
//    }

    /**
     * @Route(
     *     path="/api/admins/promos",
     *     methods={"GET"},
     *     name="getPromos"
     * )
    */
    public function getPromos()
    {
        if (!$this->isGranted("VIEW",new Promos()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $promos = $this->promosRepository->findBy(["isDeleted" => false]);
        $promos = $this->serializer->normalize($promos,null,["groups" => [self::PROMO_READ]]);
        return $this->json($promos,Response::HTTP_OK);
    }

    /**
     * @Route(
     *     path="/api/admins/promos/principal",
     *     methods={"GET"},
     *     name="getGrpPrincipaux"
     * )
     */
    public function getGrpPrincipaux()
    {
        if (!$this->isGranted("VIEW",new Promos()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $promos = $this->promosRepository->findBy(["isDeleted" => false]);
        $promos = $this->serializer->normalize($promos,null,["groups" => [self::PROMO_READ,self::PROMO_APPRENANT_READ]]);
        return $this->json($promos,Response::HTTP_OK);
    }

    /**
     * @Route(
     *     path="/api/admins/promos/apprenants/attente",
     *     methods={"GET"},
     *     name="getWaitingStudents"
     * )
     */
    public function getWaitingStudents()
    {
        if (!$this->isGranted("VIEW",new Promos()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $promos = $this->promosRepository->findBy(["isDeleted" => false]);
        $promoTab = [];
        $promoTab["referentiels"] = [];
        $promoTab["apprenants"] = [];
        foreach ($promos as $promo)
        {
            $groupes = $promo->getGroupes();
            $promoTab["referentiels"][] = $promo->getReferentiel();
            foreach ($groupes as $groupe)
            {
                $students = $groupe->getApprenant();
                foreach ($students as $student)
                {
                    if (!$student->getIsConnected())
                    {
                        $promoTab["apprenants"][] = $student;
                    }
                }
            }
        }
        $promoTab = $this->serializer->normalize($promoTab,null,["groups" => [self::PROMO_READ,self::PROMO_APPRENANT_READ]]);
        return $this->json($promoTab,Response::HTTP_OK);
    }

    /**
     * @Route(
     *     path="/api/admins/promos/{id}",
     *     methods={"GET"},
     *     name="getPromo"
     * )
     */
    public function getPromo($id)
    {
        if (!$this->isGranted("VIEW",new Promos()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $promo = $this->promosRepository->findOneBy(["id" => $id]);
        if ($promo && !$promo->getIsDeleted())
        {
            $promo = $this->serializer->normalize($promo,null,["groups" => [self::PROMO_READ]]);
            return $this->json($promo,Response::HTTP_OK);
        }
        return $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/admins/promos/{id}/principal",
     *     methods={"GET"},
     *     name="getPrincipal"
     * )
     */
    public function getPrincipal($id)
    {
        if (!$this->isGranted("VIEW",new Promos()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $promoTab = [];
        $promo = $this->promosRepository->findOneBy(["id" => $id]);
        if ($promo && !$promo->getIsDeleted())
        {
            $groupes = $promo->getGroupes();
            foreach ($groupes as $groupe )
            {
                if($groupe->getType() == "principal")
                {
                    $promoTab["apprenants"] = $groupe->getApprenant();
                    break;
                }
            }
            $promoTab["referentiel"] = $promo->getReferentiel();
            $promoTab["formateur"] = $promo->getFormateur();
            if(!isset($promoTab["apprenants"]))
            {
                return $this->json(["message" => "Pas de groupe princpal pour cette promo."]);
            }
            return $this->json($promoTab,Response::HTTP_OK);
        }
        return $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/admins/promos/{id}/referentiels",
     *     methods={"GET"},
     *     name="getReferentielInPromo"
     * )
     */
    public function getReferentielInPromo($id)
    {
        if (!$this->isGranted("VIEW",new Promos()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $promo = $this->promosRepository->findOneBy(["id" => $id]);
        if ($promo && !$promo->getIsDeleted())
        {
            $promoTab = [];
            $referentiel = $promo->getReferentiel();
            $grpeCompetences = $referentiel->getGroupeCompetence();
            $promoTab["referentiel"] = $referentiel;
            $promoTab["promo"] = $referentiel->getPromos();
            $promoTab["grpeCompetences"] = $grpeCompetences;
            $promoTab["competences"] = [];
            foreach ($grpeCompetences  as $groupeCompetence)
            {
                $competences = $groupeCompetence->getCompetences();
                foreach ($competences as $competence)
                {
                    $promoTab["competences"][] = $competence;
                }
            }
            return $this->json($promoTab,Response::HTTP_OK);
        }
        return $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/admins/promos/{id}/apprenants/attente",
     *     methods={"GET"},
     *     name="getWaitingStudent"
     * )
     */
    public function getWaitingStudent($id)
    {
        if (!$this->isGranted("VIEW",new Promos()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $promo = $this->promosRepository->findOneBy(["id" => $id]);
        if ($promo && !$promo->getIsDeleted())
        {
            $promoTab = [];
            $groupes = $promo->getGroupes();
            $promoTab["referentiel"] = $promo->getReferentiel();
            $promoTab["apprenants"] = [];
            foreach ($groupes as $groupe)
            {
                $students = $groupe->getApprenant();
                foreach ($students as $student)
                {
                    if(!$student->getIsConnected())
                    {
                        $promoTab["apprenants"][] = $student;
                    }
                }
            }
            return $this->json($promoTab,Response::HTTP_OK);
        }
        return $this->json(["message" => "Ressource inexistante."],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/admins/promos/{idPromo}/groupes/{idGroupe}/apprenants",
     *     methods={"GET"},
     *     name="getStudentsInPromo"
     * )
     */
    public function getStudentsInPromo($idPromo,$idGroupe)
    {
        if (!$this->isGranted("VIEW",new Promos()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $promo = $this->promosRepository->findOneBy(["id" => $idPromo]);
        if($promo && !$promo->getIsDeleted())
        {
            $promoTab = [];
            $groupes = $promo->getGroupes();
            $promoTab["referentiel"] = $promo->getReferentiel();
            foreach ($groupes as $groupe )
            {
                if($groupe->getId() == $idGroupe)
                {
                    $promoTab["groupe"] = $groupe;
                    $promoTab["apprenants"] = $groupe->getApprenant();
                    $promoTab["promo"] = $groupe->getPromos();
                }
            }
            if(!isset($promoTab["groupe"]))
            {
                return $this->json(["message" => "Cette groupe n'exite pas dans ce promo."],Response::HTTP_NOT_FOUND);
            }
            return  $this->json($promoTab,Response::HTTP_OK);
        }
        return $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/admins/promos/{id}/formateurs",
     *     methods={"GET"},
     *     name="getFormateurInPromo"
     * )
     */
    public function getFormateurInPromo($id)
    {
        if (!$this->isGranted("VIEW",new Promos()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $promo = $this->promosRepository->findOneBy(["id" => $id]);
        if($promo && !$promo->getIsDeleted())
        {
            $promoTab = [];
            $promoTab["referentiel"] = $promo->getReferentiel();
            $formateurs = $promo->getFormateur();
            $promoTab["formateurs"] = $formateurs;
            $promoTab["groupes"] = [];
            foreach ($formateurs as $formateur )
            {
                $groupes = $formateur->getGroupes();
                foreach ($groupes as $groupe )
                {
                    $promoTab["groupes"][] = $groupe;
                }
            }
            return  $this->json($promoTab,Response::HTTP_OK);
        }
        return $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }
    
    /**
     * @Route(
     *     path="/api/admins/promos",
     *     methods={"POST"},
     *     name="addPromo"
     * )
     */
    public function addPromo(Request $request,\Swift_Mailer $mailer,TokenStorageInterface $tokenStorage,ReferentielRepository $referentielRepository,ApprenantRepository $apprenantRepository,FormateurRepository $formateurRepository)
    {
        if (!$this->isGranted("EDIT",new Promos()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $promoJson = $request->getContent();
        $sender = 'terangawebdevelopment@gmail.com';
        $promoTab = $this->serializer->decode($promoJson,"json");
        $referentielId = isset($promoTab["referentiel"]["id"]) ? (int)$promoTab["referentiel"]["id"] : null;
        $groupes = isset($promoTab['groupes']) ? $promoTab['groupes'] : [];
        $formateurs = isset($promoTab["formateurs"]) ? $promoTab["formateurs"] : [];
        $promoTab["referentiel"] = null;
        $promoTab['groupes'] = [];
        $promoObj = $this->serializer->denormalize($promoTab,"App\Entity\Promos");
        $referentiel = $referentielRepository->findOneBy(["id" => $referentielId]);
        $creator = $tokenStorage->getToken()->getUser();
        $promoObj->setAdmin($creator)
                 ->setEtat(true)
                 ->setIsDeleted(false)
                 ->setReferentiel($referentiel);
        $errors = $this->validator->validate($promoObj);
        if(count($errors))
        {
            return $this->json($errors,Response::HTTP_BAD_REQUEST);
        }
        if (!count($groupes))
        {
            return $this->json(["message" => "Vous devez obligatoirement ajouter un groupe principal"],Response::HTTP_BAD_REQUEST);
        }
        if(count($formateurs))
        {
            foreach ($formateurs as $formateur)
            {
                $teacherId = isset($formateur["id"]) ? $formateur["id"] : null;
                $teacher = $formateurRepository->findOneBy(["id" => $teacherId]);
                if(!$teacher)
                {
                    return $this->json(["message" => "Cette formateur n'existe pas."],Response::HTTP_NOT_FOUND);
                }
                $promoObj->addFormateur($teacher);
            }
        }
        foreach ($groupes as $groupe)
        {
            $emails = isset($groupe["apprenants"]) ? $groupe["apprenants"] : [];
            $groupe["apprenants"] = [];
            if(!count($emails))
            {
                return $this->json(["message" => "Ajouter des apprenants (leur email) dans le groupe."],Response::HTTP_BAD_REQUEST);
            }
            $unit = $this->serializer->denormalize($groupe,"App\Entity\Groupes");
            $dateCreation = new \DateTime();
            $unit->setDateCreation($dateCreation)
                 ->setIsDeleted(false);
            foreach ($emails as $email)
            {
                $student = $apprenantRepository->findOneBy(["email" => $email["email"]]);
                if(!$student)
                {
                    return $this->json(["message" => "L'apprenant ayant l'email: $email[email] n'existe pas."],Response::HTTP_NOT_FOUND);
                }
                $statistiqueCompetence = new StatistiquesCompetences();
                $student->setIsConnected(false);
                $unit->addApprenant($student);
                $message = (new \Swift_Message("Ajout apprenant au promo"))
                            ->setFrom($sender)
                            ->setTo($email["email"])
                            ->setBody(
                                $this->renderView(
                                    "emails/congratulation.html.twig",["nothing"]
                                ),
                                "text/html"
                            );
                $mailer->send($message);
                $statistiqueCompetence->setPromo($promoObj)
                                    ->setApprenant($student)
                                    ->setReferentiel($referentiel)
                                    ->setNiveau1(0)
                                    ->setNiveau2(0)
                                    ->setNiveau3(0);
                $this->manager->persist($statistiqueCompetence);
            }
            $unitErrors = $this->validator->validate($unit);
            if(count($unitErrors))
            {
                return $this->json($unitErrors,Response::HTTP_BAD_REQUEST);
            }
            $this->manager->persist($unit);
            $promoObj->addGroupe($unit);
        }
        $this->manager->persist($promoObj);
        $this->manager->flush();
        return $this->json($promoObj,Response::HTTP_CREATED);
    }

    /**
     * @Route(
     *     path="/api/admins/promos/{id}/formateurs",
     *     methods={"PUT"},
     *     name="setFormateurInPromo"
     * )
     */
    public function setFormateurInPromo($id,Request $request,UserPasswordEncoderInterface $encoder)
    {
        if (!$this->isGranted("SET",new Promos()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $promo = $this->promosRepository->findOneBy(["id" => $id]);
        $profs = [];
        if ($promo && !$promo->getIsDeleted())
        {
            $formateursJson = $request->getContent();
            $formateursTab = $this->serializer->decode($formateursJson,"json");
            $formateurs = isset($formateursTab["formateurs"]) ? $formateursTab["formateurs"] : [];
            foreach ($formateurs as $formateur)
            {
                $idFormateur = isset($formateur["id"]) ? $formateur["id"] : null;
                $teacher = $this->serializer->denormalize($formateur,"App\Entity\Formateur");
                if(!$idFormateur)
                {
                    $teacher->setIsDeleted(false);
                    $teacherErrors = $this->validator->validate($teacher);
                    if(count($teacherErrors))
                    {
                        return  $this->json($teacherErrors,Response::HTTP_BAD_REQUEST);
                    }
                    $this->manager->persist($teacher);
                    $promo->addFormateur($teacher);
                    $profs[] = $teacher;
                }else{
                    $promoTeachers = $promo->getFormateur();
                    $prof = null;
                    foreach ($promoTeachers as $promoTeacher)
                    {
                        if ($promoTeacher->getId() == $idFormateur && !$promo->getIsDeleted())
                        {
                            $prof = $promoTeacher;
                            break;
                        }
                    }
                    if ($prof)
                    {
                        $teacherErrors = $this->validator->validate($teacher);
                        if(count($teacherErrors))
                        {
                            return  $this->json($teacherErrors,Response::HTTP_BAD_REQUEST);
                        }
                        $password = $encoder->encodePassword($teacher,$teacher->getPassword());
                        $isDeleted = $teacher->getIsDeleted() == true ? true : false;
                        $prof->setNom($teacher->getNom())
                            ->setPrenom($teacher->getPrenom())
                            ->setEmail($teacher->getEmail())
                            ->setUsername($teacher->getUsername())
                            ->setPassword($password)
                            ->setIsDeleted($isDeleted);
                        $profs[] = $prof;
                    } else{
                        return $this->json(["message" => "Le formateur avec l'id : $idFormateur soit il n'existe pas, soit il n'est pas un formateur , soit il n'est pas affecte à cette promo."],Response::HTTP_NOT_FOUND);
                    }
                }
            }
            $this->manager->flush();
            return $this->json($profs,Response::HTTP_OK);
        }
        return $this->json(["message" => "Ressource inexistante."],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/admins/promos/{id}",
     *     methods={"PUT"},
     *     name="setPromo"
     * )
    */
    public function setPromo(Request $request,$id,ReferentielRepository $referentielRepository)
    {
        if (!$this->isGranted("SET",new Promos()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $oldPromo = $this->promosRepository->findOneBy(["id" => $id]);
        if($oldPromo && !$oldPromo->getIsDeleted())
        {
            $promoJson = $request->getContent();
            $promoTab = $this->serializer->decode($promoJson,"json");
            $referentiel = isset($promoTab["referentiel"]) ? $promoTab["referentiel"] : null;
            $groupes = isset($promoTab["groupes"]) ? $promoTab["groupes"] : [];
            $promoTab["formateur"] = [];
            $promoTab["groupes"] = [];
            $promoTab["referentiel"] = null;
            $promoObj = $this->serializer->denormalize($promoTab,"App\Entity\Promos");
            $promoObj = $this->setReferentiel($referentiel,$oldPromo,$referentielRepository,$promoObj);
            $groupesNumber = count($groupes);
            for ($i = 0; $i < $groupesNumber; $i++)
            {
                $idGroupe = isset($groupes[$i]["id"]) ? $groupes[$i]["id"] : null;
                $groupes[$i]["formateur"] = [];
                $groupes[$i]["apprenant"] = [];
                $groupes[$i]["promos"] = null;
                $groupes[$i] = $this->serializer->denormalize($groupes[$i],"App\Entity\Groupes");
                if (!$idGroupe)
                {
                    $groupes[$i]->setIsDeleted(false);
                    $groupeErrors = $this->validator->validate($groupes[$i]);
                    if (count($groupeErrors))
                    {
                        return  $this->json($groupeErrors,Response::HTTP_BAD_REQUEST);
                    }
                    $this->manager->persist($groupes[$i]);
                    $oldPromo->addGroupe($groupes[$i]);
                }else{
                    $groupesInPromo = $oldPromo->getGroupes();
                    foreach ($groupesInPromo as $groupeInPromo)
                    {
                        if($groupeInPromo->getId() == $idGroupe && !$groupeInPromo->getIsDeleted())
                        {
                            $isDeleted = $groupes[$i]->getIsDeleted() == true ? true : false;
                            $groupeInPromo->setNom((string) $groupes[$i]->getNom())
                                          ->setIsDeleted($isDeleted)
                                          ->setStatut((string) $groupes[$i]->getStatut())
                                          ->setType((string) $groupes[$i]->getType());
                        }
                    }
                }
            }
            $promoErrors = $this->validator->validate($promoObj);
            if (count($promoErrors))
            {
                return $this->json($promoErrors,Response::HTTP_BAD_REQUEST);
            }
            $isDeleted = $promoObj->getIsDeleted() == true ? true : false;
            $oldPromo->setLangue($promoObj->getLangue())
                    ->setDateDebut($promoObj->getDateDebut())
                    ->setEtat((bool)$promoObj->getEtat())
                    ->setIsDeleted($isDeleted)
                    ->setDateFinProvisoire($promoObj->getDateFinProvisoire())
                    ->setTitre($promoObj->getTitre())
                    ->setFabrique($promoObj->getFabrique())
                    ->setDescription($promoObj->getDescription())
                    ->setLieu((string) $promoObj->getLieu());
            $this->manager->flush();
            return $this->json($oldPromo,Response::HTTP_OK);
        }
        return $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/admins/promos/{idPromo}/groupes/{idGroupe}",
     *     methods={"PUT"},
     *     name="setStatutGroupe"
     * )
     */
    public function setStatutGroupe($idPromo,$idGroupe,Request $request)
    {
        if (!$this->isGranted("SET",new Promos()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $promo = $this->promosRepository->findOneBy(["id" => $idPromo]);
        if($promo && !$promo->getIsDeleted())
        {
            $groupes = $promo->getGroupes();
            foreach ($groupes as $groupe)
            {
                if(($groupe->getId() == $idGroupe) && !$groupe->getIsDeleted())
                {
                    $groupeJson = $request->getContent();
                    $groupeTab = $this->serializer->decode($groupeJson,"json");
                    $statuts = ["en cours","cloture"];
                    $statutRequest = isset($groupeTab["statut"]) ? $groupeTab["statut"] : null;
                    if (!in_array($statutRequest,$statuts))
                    {
                        return  $this->json(["message" => "Le statut du groupe est soit 'en cours' soit 'cloture'."],Response::HTTP_BAD_REQUEST);
                    }
                    $groupe->setStatut($statutRequest);
                    $this->manager->flush();
                    return $this->json($groupe,Response::HTTP_OK);
                }
            }
        }
        return $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }

    private function setReferentiel($referentiel,$oldPromo,$referentielRepository,$promoObj)
    {
        if($referentiel == null)
        {
            $referentiel = $oldPromo->getReferentiel();
            $promoObj->setReferentiel($referentiel);
        }
        else
        {
            $referentielId = isset($referentiel["id"]) ? $referentiel["id"] : null;
            $referentiel["promos"] = [];
            $referentiel["groupeCompetence"] = [];
            $referentiel = $this->serializer->denormalize($referentiel,"App\Entity\Referentiel");
            if(!$referentielId)
            {
                return $this->json(["message" => "veuillez specifier l'id du referentiel"],Response::HTTP_BAD_REQUEST);
            }
            if($oldPromo->getReferentiel()->getId() != $referentielId)
            {
                return $this->json(["message" => "Ce referentiel n'est pas dans cette promo."],Response::HTTP_BAD_REQUEST);
            }
            $referentielErrors = $this->validator->validate($referentiel);
            if (count($referentielErrors))
            {
                return $this->json($referentielErrors,Response::HTTP_BAD_REQUEST);
            }
            $oldReferentiel = $referentielRepository->findOneBy(["id" => $referentielId]);
            $referentiel->setId($oldReferentiel->getId());
            if($oldReferentiel != $referentiel)
            {
                $oldReferentiel->setLibelle((string)$referentiel->getLibelle())
                    ->setPresentation((string)$referentiel->getPresentation())
                    ->setProgramme((string)$referentiel->getProgramme())
                    ->setCritereAdmission((string)$referentiel->getCritereAdmission())
                    ->setCritereEvaluation((string)$referentiel->getCritereEvaluation())
                    ->setIsDeleted((bool)$referentiel->getIsDeleted());
                $promoObj->setReferentiel($oldReferentiel);
            }
        }
        return $promoObj;
    }

}
