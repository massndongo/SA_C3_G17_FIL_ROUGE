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
     *     name="getBriefs"
     * )
    */
    public function getBriefs()
    {
        if (!$this->isGranted("VIEW",new Brief()))
        {
            return $this->json(["message" => self::ACCESS_DENIED]);
        }
        $briefs = $this->briefRepository->findAll();
        return  $this->json($briefs,Response::HTTP_OK);
    }

    /**
     * @Route(
     *     path="/api/formateurs/promos/{idPromo<\d+>}/briefs/{idBrief<\d+>}",
     *     methods={"GET"},
     *     name="getBriefInPromo"
     * )
     */
    public function getBriefInPromo($idPromo,$idBrief)
    {
        if (!$this->isGranted("VIEW",new Brief()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $promo = $this->promosRepository->findOneBy(["id" => $idPromo]);
        if($promo && !$promo->getIsDeleted())
        {
            $brief = $this->briefRepository->findOneBy(["id" => $idBrief]);
            if ($brief)
            {
                $promoBriefs = $brief->getPromoBriefs();
                foreach ($promoBriefs as $promoBrief)
                {
                    if ($promoBrief->getBrief() == $brief)
                    {
                        return $this->json($brief,Response::HTTP_OK);
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
     *     name="getBbriefsFormateur"
     * )
     */
    public function getBbriefsFormateur($id)
    {
        if (!$this->isGranted("VIEW",new Brief()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $promo = $this->promosRepository->findOneBy(["id" => $id]);
        if($promo && !$promo->getIsDeleted())
        {
            $formateur = $this->connectedTeacher;
            $promos = $formateur->getPromos()->getValues();
            $message = "";
            $status = null;
            if(in_array($promo,$promos))
            {
                $role = $formateur->getRoles()[0];
                if($role == "ROLE_FORMATEUR")
                {
                    $brouillons = [];
                    $briefs = $formateur->getBriefs();
                    $message = $briefs;
                    $status = Response::HTTP_OK;
                }else{
                    $message = "Seul le formateur peut lister ses briefs";
                    $status = Response::HTTP_FORBIDDEN;
                }
            }else{
                $message = "Ce formateur n'es pas affecté à cette promo.";
                $status = Response::HTTP_NOT_FOUND;
            }
            return  $this->json(["message" => $message],$status);
        }
        return  $this->json(["message" => self::RESOURCE_NOT_FOUND]);
    }

    /**
     * @Route(
     *     path="/api/formateurs/promos/{idPromo}/groupes/{idGroupe}/briefs",
     *     methods={"GET"},
     *     name="getBriefsInGroupe"
     * )
     */
    public function getBriefsInGroupe($idPromo,$idGroupe)
    {
        if(!$this->isGranted("VIEW",new Brief()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $promo = $this->promosRepository->findOneBy(["id" => $idPromo]);
        if ($promo && !$promo->getIsDeleted())
        {
            $groupe = $this->groupesRepository->findOneBy(["id" => $idGroupe]);
            $message = '';
            $status = null;
            if ($groupe)
            {
                $groupesInPromo = $promo->getGroupes()->getValues();
                if (in_array($groupe,$groupesInPromo))
                {
                    $message = $groupe->getBriefs();
                    $status = Response::HTTP_OK;
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
     *     name="getBriefsBrouillonFormateur"
     * )
     */
    public function getBriefsBrouillonFormateur($id,FormateurRepository $formateurRepository)
    {
        if (!$this->isGranted("VIEW",new Brief()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $formateur = $formateurRepository->findOneBy(["id" => $id]);
        if($formateur && !$formateur->getIsDeleted())
        {
            $result = $this->filterBriefWithStatus($formateur,"brouillon");
            $message = $result[0];
            $status = $result[1];
            return  $this->json(["message" => $message],$status);
        }
        return  $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/formateurs/{id<\d+>}/briefs/valide",
     *     methods={"GET"},
     *     name="getFormateurValideBriefs"
     * )
     */
    public function getFormateurValideBriefs($id,FormateurRepository $formateurRepository)
    {
        if (!$this->isGranted("VIEW",new Brief()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $formateur = $formateurRepository->findOneBy(["id" => $id]);
        if ($formateur && !$formateur->getIsDeleted())
        {
            $result = $this->filterBriefWithStatus($formateur,"valide");
            $message = $result[0];
            $status = $result[1];
            return  $this->json(["message" => $message],$status);
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
        return [$briefsValides,Response::HTTP_OK];
    }
}