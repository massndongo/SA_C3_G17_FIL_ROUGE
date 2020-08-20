<?php

namespace App\Controller;

use App\Entity\Brief;
use App\Repository\BriefRepository;
use App\Repository\PromosRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BriefController extends AbstractController
{
    /**
     * @Route("/api/formateurs/briefs", name="getBriefs",methods={"GET"})
     */
    public function getBriefs(BriefRepository $briefRepository)
    {
        $brief = new Brief();
        if (!$this->isGranted("VIEW",$brief))
        {
            return $this->json(["message" => "Vous n'avez pas accés à cette ressource"],Response::HTTP_FORBIDDEN);
        }
        $briefs = $briefRepository->findAll();
        return $this->json($briefs,Response::HTTP_OK);
    }

    /**
     * @Route("/api/formateurs/promos/idPromo/briefs/idBrief",name="getBriefInPromo",methods={"GET"})
    */
    public function getBriefInPromo($idPromo,$idBrief,PromosRepository $promosRepository,BriefRepository $briefRepository)
    {
        $brief = new Brief();
        if ($this->isGranted("VIEW",$brief))
        {
            return $this->json(["message" => "Vous n'avez pas accés à cette ressource"],Response::HTTP_FORBIDDEN);
        }
        $promo = $promosRepository->findOneBy(["id" => $idPromo]);
        if($promo && !$promo->getIsDeleted())
        {
            $brief = $briefRepository->findOneBy(["id" =>$idBrief]);
            if($brief)
            {
                $promoBriefs = $promo->getPromoBriefs();
                foreach ($promoBriefs as $promoBrief)
                {
                    if($promoBrief->getBrief() == $brief)
                    {
                        return $this->json($brief,Response::HTTP_OK);
                    }
                }
            }
        }
        return $this->json(["message" => "ressource inexistante."],Response::HTTP_NOT_FOUND);
    }
}
