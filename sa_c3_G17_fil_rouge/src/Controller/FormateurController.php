<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class FormateurController extends AbstractController
{
    /**
     * @Route(
     *     path="/api/formateurs/{id<\d+>}",
     *     methods={"GET"},
     *     defaults={
     *          "__controller"="App\Controller\FormateurController::getFormateur",
     *          "__api_resource_class"=User::class,
     *          "__api_collection_operation_name"="get_formateur"
     *     }
     * )
     */
    public function getFormateur(User $formateur)
    {
        $idFormateurProfil = 3;
        if($formateur->getProfil()->getId() == $idFormateurProfil){
            return $this->json($formateur,Response::HTTP_OK);
        }else{
            return $this->json(["message" => "Vous n'avez pas acces à cette ressource"],Response::HTTP_FORBIDDEN);
        }
    }

     /**
     * @Route(
     *     path="/api/formateurs/{id<\d+>}",
     *     methods={"PUT"},
     *     defaults={
     *          "__controller"="App\Controller\FormateurController::setFormateur",
     *          "__api_resource_class"=User::class,
     *          "__api_collection_operation_name"="set_formateur"
     *     }
     * )
     */
    public function setFormateur(User $set_formateur,EntityManagerInterface $manager,Request $request,UserRepository $userRepository,SerializerInterface $serializer,ValidatorInterface $validator,UserPasswordEncoderInterface $encoder)
    {
        $formateur_profil = $userRepository->findOneBy([
            "id" => 3
        ]);
        $formateur = $request->request->all();
        $avatar = $request->files->get("avatar");
        $avatar = fopen($avatar->getRealPath(),"rb");
        $formateur["avatar"] = $avatar;
        $formateur = $serializer->denormalize($formateur,"App\Entity\User");
        $errors = $validator->validate($formateur);
        if (count($errors)){
            $errors = $serializer->serialize($errors,"json");
            return new JsonResponse($errors,Response::HTTP_BAD_REQUEST,[],true);
        }else if($formateur->getProfil()->getId() != $formateur_profil->getId()){
            $errors = [
                "message" => "Veuillez choisir le profil formateur"
            ];
            $errors = $serializer->serialize($errors,"json");
            return new JsonResponse($errors,Response::HTTP_BAD_REQUEST,[],true);
        }
        $password = $formateur->getPassword();
        $formateur->setPassword($encoder->encodePassword($formateur,$password));
        if($formateur->getPassword() != $set_formateur->getPassword()){
            $set_formateur->setPassword($formateur->getPassword());
        }
        if($formateur->getUsername() != $set_formateur->getUsername()){
            $set_formateur->setUsername($formateur->getUsername());
        }
        if($formateur->getAvatar() != $set_formateur->getAvatar()){
            $set_formateur->setAvatar($formateur->getAvatar());
        }
        $manager->flush();
        fclose($avatar);
        return $this->json($formateur,Response::HTTP_CREATED);
    }
}
