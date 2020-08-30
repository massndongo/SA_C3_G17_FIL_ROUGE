<?php

namespace App\Controller;

use App\Entity\Formateur;
use App\Entity\User;
use App\Repository\FormateurRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;

class FormateurController extends AbstractController
{
    private $serializer,
            $formateurRepository;

    private const ACCESS_DENIED = "Vous n'avez pas accés à cette ressource.",
                RESOURCE_NOT_FOUND = "Ressource inexistante.",
                FORMATEUR_READ = "formateur:read";
    public function __construct(SerializerInterface $serializer,FormateurRepository $formateurRepository)
    {
        $this->serializer = $serializer;
        $this->formateurRepository = $formateurRepository;
    }
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
    public function getFormateur($id)
    {
        if (!$this->isGranted("VIEW",new Formateur()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $formateur = $this->formateurRepository->findOneBy(["id" => $id]);
        if ($formateur && !$formateur->getIsDeleted())
        {
            $formateur = $this->serializer->normalize($formateur,null,["groups" => [self::FORMATEUR_READ]]);
            return $this->json($formateur,Response::HTTP_OK);
        }
        return $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
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
    public function getFormateurs(FormateurRepository $formateurRepository, SerializerInterface $serializer)
    {
        if (!$this->isGranted("VIEW",new Formateur()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $formateurs = $formateurRepository->findAll();
        $formateurs = $this->serializer->normalize($formateurs,null,["groups" => [self::FORMATEUR_READ]]);
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
    public function setFormateur($id)
    {
        if (!$this->isGranted("EDIT",new Formateur()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $formateur = $this->formateurRepository->findOneBy(["id" => $id]);
        if($formateur)
        {
            return $this->json(["message" => "hii"],Response::HTTP_OK);
        }
        return $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_FORBIDDEN);
    }
}
