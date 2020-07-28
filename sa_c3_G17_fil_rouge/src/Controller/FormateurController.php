<?php

namespace App\Controller;

use App\Entity\Formateur;
use App\Entity\User;
use App\Repository\FormateurRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FormateurController extends AbstractController
{
    /**
     * @Route(
     *     path="/api/formateurs/{id<\d+>}",
     *     methods={"GET"},
     *     defaults={
     *          "__controller"="App\Controller\FormateurController::getFormateur",
     *          "__api_resource_class"=Formateur::class,
     *          "__api_collection_operation_name"="get_formateur"
     *     }
     * )
     */
    public function getFormateur(User $formateur)
    {
        if($formateur->getRoles()[0] == "ROLE_FORMATEUR"){
            return $this->json($formateur,Response::HTTP_OK);
        }else{
            return $this->json(["message" => "Vous n'avez pas acces à cette ressource"],Response::HTTP_FORBIDDEN);
        }
    }
    /**
     * @Route(
     *     path="/api/formateurs",
     *     methods={"GET"},
     *     defaults={
     *          "__controller"="App\Controller\FormateurController::getFormateurs",
     *          "__api_resource_class"=Formateur::class,
     *          "__api_collection_operation_name"="get_formateurs"
     *     }
     * )
    */
    public function getFormateurs(FormateurRepository $formateurRepository)
    {
        $formateurs = $formateurRepository->findAll();
        return $this->json($formateurs,Response::HTTP_OK);
    }
     /**
     * @Route(
     *     path="/api/formateurs/{id<\d+>}",
     *     methods={"PUT"},
     *     defaults={
     *          "__controller"="App\Controller\FormateurController::setFormateur",
     *          "__api_resource_class"=Formateur::class,
     *          "__api_collection_operation_name"="set_formateur"
     *     }
     * )
     */
    public function setFormateur(Formateur $formateur)
    {
        if($formateur->getRoles()[0] == "ROLE_FORMATEUR"){
        }
        return $this->json(["message" => "Vous n'avez pas acces à cette ressource"],Response::HTTP_FORBIDDEN);
    }
}
