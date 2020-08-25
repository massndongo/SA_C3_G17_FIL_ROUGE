<?php

namespace App\Controller;

use App\Entity\Brief;
use App\Repository\BriefRepository;
use App\Repository\FormateurRepository;
use App\Repository\GroupesRepository;
use App\Repository\PromosRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BriefController extends AbstractController
{
    private $briefRepository,
        $promosRepository,
        $groupesRepository,
        $connectedTeacher;
    private const ACCESS_DENIED = "Vous n'avez pas accés à cette ressource.",
        RESOURCE_NOT_FOUND = "Ressource inexistante.";

    public function __construct(BriefRepository $briefRepository, PromosRepository $promosRepository, GroupesRepository $groupesRepository, TokenStorageInterface $tokenStorage)
    {
        $this->briefRepository = $briefRepository;
        $this->promosRepository = $promosRepository;
        $this->groupesRepository = $groupesRepository;
        $this->connectedTeacher = $tokenStorage->getToken()->getUser();
    }

    /**
     * @Route(
     *     path="/api/formateurs/briefs",
     *     methods={"GET"},
     *     name="getBriefs",
     *     defaults={
     *          "_controller"="\App\Controller\BriefController::getBriefs",
     *          "_api_resource_class"=Brief::class,
     *          "_api_collection_operation_name"="getBriefs"
     *      }
     * )
    */
    public function getBriefs()
    {
        if (!$this->isGranted("VIEW",new Brief()))
        {
            return $this->json(["message" => self::ACCESS_DENIED]);
        }
        $briefs = $this->briefRepository->findAll();
        return  $briefs;
    }

    /**
     * @Route(
     *     path="/api/formateurs/promos/{idPromo<\d+>}/briefs/{idBrief<\d+>}",
     *     methods={"GET"},
     *     name="getBriefInPromo",
     *     defaults={
     *          "_controller"="\App\Controller\BriefController::getBriefInPromo",
     *          "_api_resource_class"=Brief::class,
     *          "_api_collection_operation_name"="getBriefInPromo"
     *      }
     * )
     */
    public function getBriefInPromo($idPromo,$idBrief)
    {
        if (!$this->isGranted("VIEW",new Brief()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $promo = $this->promosRepository->findOneBy(["id" => (int)$idPromo]);
        if($promo && !$promo->getIsDeleted())
        {
            $brief = $this->briefRepository->findOneBy(["id" => (int)$idBrief]);
            if ($brief)
            {
                $promoBriefs = $brief->getPromoBriefs();
                foreach ($promoBriefs as $promoBrief)
                {
                    if ($promoBrief->getBrief() == $brief)
                    {
                        return $brief;
                    }
                }
            }
        }
        return  $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/formateurs/promos/{id}/briefs",
     *     methods={"GET"},
     *     name="getBbriefsFormateur",
     *     defaults={
     *          "_controller"="\App\Controller\BriefController::getBbriefsFormateur",
     *          "_api_resource_class"=Brief::class,
     *          "_api_collection_operation_name"="getBriefsFormateur"
     *      }
     * )
     */
    public function getBbriefsFormateur($id)
    {
        if (!$this->isGranted("VIEW",new Brief()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $promo = $this->promosRepository->findOneBy(["id" => (int)$id]);
        if($promo && !$promo->getIsDeleted())
        {
            $promoBriefs = $promo->getPromoBriefs();
            $briefs = [];
            foreach ($promoBriefs as $promoBrief)
            {
                $briefs[] = $promoBrief->getBrief();
            }
            return $briefs;
        }
        return  $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/formateurs/promos/{idPromo}/groupes/{idGroupe}/briefs",
     *     methods={"GET"},
     *     name="getBriefsInGroupe",
     *     defaults={
     *          "_controller"="\App\Controller\BriefController::getBriefsInGroupe",
     *          "_api_resource_class"=Brief::class,
     *          "_api_collection_operation_name"="getBriefsInGroupe"
     *      }
     * )
     */
    public function getBriefsInGroupe($idPromo,$idGroupe)
    {
        if(!$this->isGranted("VIEW",new Brief()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $promo = $this->promosRepository->findOneBy(["id" => (int)$idPromo]);
        if ($promo && !$promo->getIsDeleted())
        {
            $groupe = $this->groupesRepository->findOneBy(["id" => (int)$idGroupe]);
            $message = '';
            $status = null;
            if ($groupe)
            {
                $groupesInPromo = $promo->getGroupes()->getValues();
                if (in_array($groupe,$groupesInPromo))
                {
                    return $groupe->getBriefs();
                }
                else{
                    $message = "Ce groupe n'est pas dans cette promo.";
                    $status = Response::HTTP_NOT_FOUND;
                }
            }
            else{
                $message = "Ce groupe est introuvable .";
                $status = Response::HTTP_NOT_FOUND;
            }
            return $this->json(["message" => $message],$status);
        }
        return  $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/formateurs/{id<\d+>}/briefs/brouillons",
     *     methods={"GET"},
     *     name="getBriefsBrouillonFormateur",
     *     defaults={
     *          "_controller"="\App\Controller\BriefController::getBriefsBrouillonFormateur",
     *          "_api_resource_class"=Brief::class,
     *          "_api_collection_operation_name"="getBriefsBrouillonFormateur"
     *      }
     * )
     */
    public function getBriefsBrouillonFormateur($id,FormateurRepository $formateurRepository)
    {
        if (!$this->isGranted("VIEW",new Brief()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $formateur = $formateurRepository->findOneBy(["id" => (int)$id]);
        if($formateur && !$formateur->getIsDeleted())
        {
            $result = $this->filterBriefWithStatus($formateur,"brouillon");
            $message = $result["message"];
            return  $message;
        }
        return  $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/formateurs/{id<\d+>}/briefs/valide",
     *     methods={"GET"},
     *     name="getFormateurValideBriefs",
     *     defaults={
     *          "_controller"="\App\Controller\BriefController::getBriefsBrouillonFormateur",
     *          "_api_resource_class"=Brief::class,
     *          "_api_collection_operation_name"="getBriefsBrouillonFormateur"
     *      }
     * )
     */
    public function getFormateurValideBriefs($id,FormateurRepository $formateurRepository)
    {
        if (!$this->isGranted("VIEW",new Brief()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $formateur = $formateurRepository->findOneBy(["id" => (int)$id]);
        if ($formateur && !$formateur->getIsDeleted())
        {
            $result = $this->filterBriefWithStatus($formateur,"valide");
            $message = $result["message"];
            return  $message;
        }

        return  $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }

    private function filterBriefWithStatus($formateur,$statusBrief)
    {
        $briefsValides = [];
        $briefs = $formateur->getBriefs();
        foreach ($briefs as $brief)
        {
            if ($brief->getStatutBrief() == $statusBrief)
            {
                $briefsValides[] = $brief;
            }
        }
        return ["message" =>$briefsValides,"status" =>Response::HTTP_OK];
    }

    /**
     * @Route(
     *     path="/api/apprenants/promos/{id<\d+>}/briefs",
     *     methods={"GET"},
     *     name="getBriefsApprenant",
     *     defaults={
     *          "_controller"="\App\Controller\BriefController::getBriefsApprenant",
     *          "_api_resource_class"=Brief::class,
     *          "_api_collection_operation_name"="getBriefsApprenant"
     *      }
     * )
     */
    public function getBriefsApprenant($id)
    {
        if(!$this->isGranted("VIEW",new Brief()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $promo = $this->promosRepository->findOneBy(["id" => (int)$id]);
        if ($promo && !$promo->getIsDeleted())
        {
            $briefAssigneApprenants = [];
            $promoBriefs = $promo->getPromoBriefs();
            foreach ($promoBriefs as $promoBrief)
            {
                $brief = $promoBrief->getBrief();
                if($brief->getStatutBrief() == "assigne")
                {
                    $briefAssigneApprenants[] = $brief;
                }
            }
            return $briefAssigneApprenants;
        }
        return  $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }
}