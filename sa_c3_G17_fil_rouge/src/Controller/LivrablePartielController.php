<?php

namespace App\Controller;

use App\Entity\Referentiel;
use App\Entity\GroupeCompetence;
use App\Repository\PromosRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReferentielRepository;
use App\Repository\LivrablePartielRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\GroupeCompetenceRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Repository\ApprenantRepository;
use App\Repository\StatistiquesCompetencesRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LivrablePartielController extends AbstractController
{
    /**
     * @Route(
     *     path="api/formateurs/promo/{id}/referentiel/{idr}/competences",
     *     methods={"GET"},
     *     name="getApprenantCompetence"
     * )
     */
    public function getApprenantCompetence(PromosRepository $promoRepository, $id, $idr, StatistiquesCompetencesRepository $statRepo)
    {
        $promo = $promoRepository->find($id);
        $referenciel = $promo->getReferentiel();
        if($referenciel->getId() == $idr)
        {
            $groupes = $promo->getGroupes();
            foreach($groupes as $groupe)
            {
                if($groupe->getType()== "Principal")
                {
                    $apprenants = $groupe->getApprenant();
                    foreach($apprenants as $apprenant)
                    {
                        $idA = $apprenant->getId();
                        $stats= $statRepo->findBy(["promos" => $id, "referentiel" => $idr, "apprenant" => $idA]);
                        foreach($stats as $stat)
                        {
                            $competence = $stat->getCompetence();
                            $niveau1 = $stat->getNiveau1();
                            $niveau2 = $stat->getNiveau2();
                            $niveau3 = $stat->getNiveau3();
                            $result[] = ["apprenant" => $apprenant, "competence" => $competence, "niveau1" =>  $niveau1, "niveau2" => $niveau2, "niveau3" => $niveau3];
                            
                        }
                        //$result[] = ["apprenant" => $apprenant, "competence" => $competence, "niveau1" =>  $niveau1, "niveau2" => $niveau2, "niveau3" => $niveau3];
                    }
                }
            }
        }
        return $this->json($result, Response::HTTP_OK, [], ["groups" => "cmpt:read"]);
    }

    /**
     * @Route(
     *     path="api/apprenants/{id}/livrablepartiels/{idl}",
     *     methods={"PUT"},
     *     name="putStatusLivrableP"
     * )
     */
    public function updateStatusLivrable(EntityManagerInterface $em,Request $request,LivrablePartielRepository $livpartielRepo, ApprenantRepository $apprenantRepository, $id, $idl)
    {
        $statut = json_decode($request->getContent(), true);
        $apprenant = $apprenantRepository->find($id);
        $livrablePartiel = $livpartielRepo->find($idl);
        if(!$livrablePartiel)
        {
            return $this->json("Ce livrable n'existe pas!", Response::HTTP_BAD_REQUEST);
        }
        if(!$apprenant)
        {
            return $this->json("Cet apprenant n'existe pas!", Response::HTTP_BAD_REQUEST);
        }
        $livrableRendus = $apprenant->getLivrableRendus();
        foreach($livrableRendus as $livrableRendu)
        {
            if($livrableRendu->getLivrablePartiel()->getId()== $idl)
            {
                $livrableRendu->setStatut($statut["statut"]);
            }
        }
        $em->flush();
        return $this->json("Modifier avec succès!", Response::HTTP_OK);

    }
}
