<?php

namespace App\Controller;

use App\Entity\Promos;
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
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function Symfony\Component\String\s;

class PromosController extends AbstractController
{
    private $manager,
            $serializer,
            $validator,
            $promosRepository;

    public function __construct(ValidatorInterface $validator,EntityManagerInterface $manager,SerializerInterface $serializer,PromosRepository $promosRepository)
    {
        $this->serializer = $serializer;
        $this->promosRepository = $promosRepository;
        $this->manager = $manager;
        $this->validator = $validator;
    }


    /**
     * @Route(
     *     path="/mail",
     *     name="mail"
     * )
     */
    public function email(\Swift_Mailer $mailer,GroupeCompetenceRepository $repository)
    {

        $sender = 'terangawebdevelopment@gmail.com';
        $receiver = "petitpapatoure98@gmail.com";
        $message = (new \Swift_Message("Ajout apprenant au promo"))
            ->setFrom($sender)
            ->setTo($receiver)
            ->setBody(
                $this->renderView(
                    "emails/congratulation.html.twig",["nothing"]
                ),
                "text/html"
            );
        $mailer->send($message);
        $grpeCompetence = $repository->findOneBy(["id" => 1]);
        return $this->json($grpeCompetence,Response::HTTP_OK);
    }

    /**
     * @Route(
     *     path="/api/admin/promos",
     *     methods={"GET"},
     *     name="getPromos"
     * )
    */
    public function getPromos()
    {
        $this->canViewPromo();
        $promos = $this->promosRepository->findBy([
            "isDeleted" => false
        ]);
        $promoTab = [];
        $promoTab["referentiels"] = [];
        $promoTab["formateurs"] = [];
        $promoTab["groupes"] = [];
        foreach ($promos as $promo)
        {
            $promoTab["referentiels"][] = $promo->getReferentiel();
            $formateurs = $promo->getFormateur();
            foreach ($formateurs as $formateur)
            {
                $promoTab["formateurs"][] = $formateur;
            }
            $groupes = $promo->getGroupes();
            foreach ($groupes as $groupe)
            {
                $promoTab["groupes"][] = $groupe;
            }
        }
        return $this->json($promoTab,Response::HTTP_OK);
    }

    /**
     * @Route(
     *     path="/api/admin/promos/principal",
     *     methods={"GET"},
     *     name="getGrpPrincipaux"
     * )
     */
    public function getGrpPrincipaux()
    {
        $this->canViewPromo();
        $promos = $this->promosRepository->findBy([
            "isDeleted" => false
        ]);
        $promoTab = [];
        $promoTab["referentiels"] = [];
        $promoTab["apprenants"] = [];
        $promoTab["formateurs"] = [];
        foreach ($promos as $promo)
        {
            $groupes = $promo->getGroupes();
            $promoTab["referentiels"][] = $promo->getReferentiel();
            $promoTab["formateurs"][] = $promo->getReferentiel();
            foreach ($groupes as $groupe)
            {
                if (!$groupe->getIsDeleted() && $groupe->getType() == "principal")
                {
                    $promoTab["apprenants"][] = $groupe->getApprenant();
                }
            }
        }
        return $this->json($promoTab,Response::HTTP_OK);
    }

    /**
     * @Route(
     *     path="/api/admin/promos/apprenants/attente",
     *     methods={"GET"},
     *     name="getWaitingStudents"
     * )
     */
    public function getWaitingStudents()
    {
        $this->canViewPromo();
        $promos = $this->promosRepository->findBy([
            "isDeleted" => false
        ]);
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
        return $this->json($promoTab,Response::HTTP_OK);
    }

    /**
     * @Route(
     *     path="/api/admin/promos/{id}",
     *     methods={"GET"},
     *     name="getPromo"
     * )
     */
    public function getPromo($id,PromosRepository $promosRepository)
    {
        $this->canViewPromo();
        $promo = $promosRepository->findOneBy(["id" => $id]);
        if ($promo && !$promo->getIsDeleted())
        {
            return $this->json($promo,Response::HTTP_OK);
        }
        return $this->json(["message" => "Ressource inexistante."],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/admin/promos/{id}/principal",
     *     methods={"GET"},
     *     name="getPrincipal"
     * )
     */
    public function getPrincipal($id,PromosRepository $promosRepository)
    {
        $this->canViewPromo();
        $promoTab = [];
        $promo = $promosRepository->findOneBy(["id" => $id]);
        if ($promosRepository && !$promo->getIsDeleted())
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
        return $this->json(["message" => "Ressource inexistante."],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/admin/promos/{id}/referentiels",
     *     methods={"GET"},
     *     name="getReferentiel"
     * )
     */
    public function getReferentiel($id,PromosRepository $promosRepository)
    {
        $this->canViewPromo();
        $promo = $promosRepository->findOneBy(["id" => $id]);
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
        return $this->json(["message" => "Ressource inexistante."],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/admin/promos/{id}/apprenants/attente",
     *     methods={"GET"},
     *     name="getWaitingStudent"
     * )
     */
    public function getWaitingStudent($id,PromosRepository $promosRepository)
    {
        $this->canViewPromo();
        $promo = $promosRepository->findOneBy(["id" => $id]);
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
     *     path="/api/admin/promos/{idPromo}/groupes/{idGroupe}/apprenants",
     *     methods={"GET"},
     *     name="getStudentsInPromo"
     * )
     */
    public function getStudentsInPromo($idPromo,$idGroupe,PromosRepository $promosRepository)
    {
        $this->canViewPromo();
        $promo = $promosRepository->findOneBy(["id" => $idPromo]);
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
        return $this->json(["message" => "Ressource inexistante."],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/admin/promos/{id}/formateurs",
     *     methods={"GET"},
     *     name="getFormateurInProm"
     * )
     */
    public function getFormateurInProm($id,PromosRepository $promosRepository)
    {
        $this->canViewPromo();
        $promo = $promosRepository->findOneBy(["id" => $id]);
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
        return $this->json(["message" => "Ressource inexistante."],Response::HTTP_NOT_FOUND);
    }
    
    /**
     * @Route(
     *     path="/api/admin/promos",
     *     methods={"POST"},
     *     name="addPromo"
     * )
     */
    public function addPromo(Request $request,\Swift_Mailer $mailer,TokenStorageInterface $tokenStorage,ReferentielRepository $referentielRepository,ApprenantRepository $apprenantRepository,FormateurRepository $formateurRepository)
    {
        $promo = new Promos();
        if (!$this->isGranted("EDIT",$promo))
        {
            return $this->json(["message" => "Vous n'avez pas access à cette Ressource"],Response::HTTP_FORBIDDEN);
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
                $mailerStatus = $mailer->send($message);
            }
            $unitErrors = $this->validator->validate($unit);
            if(count($unitErrors))
            {
                return $this->json($unitErrors,Response::HTTP_BAD_REQUEST);
            }
            $this->manager->persist($unit);
            $promoObj->addGroupe($unit);
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
        };
        $this->manager->persist($promoObj);
        $this->manager->flush();
        return $this->json($promoObj,Response::HTTP_CREATED);
    }


    private function canViewPromo()
    {
        $promo = new Promos();
        if (!$this->isGranted("VIEW",$promo))
            return $this->json(["message" => "Vous n'avez pas access à cette Ressource"],Response::HTTP_FORBIDDEN);
    }
}
