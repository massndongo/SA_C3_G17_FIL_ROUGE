<?php

namespace App\Controller;
use App\Entity\Apprenant;
use App\Entity\User;
use App\Repository\ApprenantRepository;
use App\Repository\ProfilRepository;
use App\Repository\PromosRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApprenantController extends AbstractController
{
    private const ACCESS_DENIED = "Vous n'avez pas accés à cette ressource.",
        RESOURCE_NOT_FOUND = "Ressource inexistante.";

    /**
     * @Route(
     *     path="/api/apprenants",
     *     methods={"GET"},
     *     defaults={
     *          "__controller"="App\Controller\ApprenantController::getApprenants",
     *          "__api_resource_class"=Apprenant::class,
     *          "__api_collection_operation_name"="get_students"
     *     }
     * )
     */
    public function getApprenants(ApprenantRepository $apprenantRepository)
    {

        $studens = $apprenantRepository->findBy([
            "isDeleted" => false
        ]);
        return $this->json($studens,Response::HTTP_OK);
    }
    /**
     * @Route(
     *     path="/api/apprenants",
     *     methods={"POST"},
     *     defaults={
     *          "__controller"="App\Controller\ApprenantController::getApprenants",
     *          "__api_resource_class"=Apprenant::class,
     *          "__api_collection_operation_name"="add_student"
     *     }
     * )
     */
    public function addStudents(Request $request,SerializerInterface $serializer,UserPasswordEncoderInterface $encoder,EntityManagerInterface $manager,ValidatorInterface $validator,ProfilRepository $profilRepository)
    {
        $opened = false;
        $profil = $profilRepository->findOneBy([
            "libelle" => "APPRENANT"
        ]);
        $student = $request->request->all();
        $avatar = $request->files->get("avatar");
        if($avatar){
            $avatar = fopen($avatar->getRealPath(),"rb");
            $student["avatar"] = $avatar;
            $opened = true;
        }
        $student = $serializer->denormalize($student,"App\Entity\Apprenant");
        $errors = $validator->validate($student);
        if (count($errors)){
            $errors = $serializer->serialize($errors,"json");
            return new JsonResponse($errors,Response::HTTP_BAD_REQUEST,[],true);
        }
        $password = $student->getPassword();
        $student->setPassword($encoder->encodePassword($student,$password));
        $student->setProfil($profil)
                ->setIsDeleted(false);
        $manager->persist($student);
        $manager->flush();
        if ($opened)
        {
            fclose($avatar);
        }
        return $this->json($student,Response::HTTP_CREATED);
    }

    /**
     * @Route(
     *     path="/api/apprenants/{id<\d+>}",
     *     methods={"GET"},
     *     defaults={
     *          "__controller"="App\Controller\ApprenantController::getStudent",
     *          "__api_resource_class"=Apprenant::class,
     *          "__api_collection_operation_name"="get_student"
     *     }
     * )
     */
    public function getStudent(User $student)
    {
        if($student->getRoles()[0] == "ROLE_APPRENANT"){
            return $this->json($student,Response::HTTP_OK);
        }else{
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * @Route(
     *     path="/api/apprenants/{id<\d+>}",
     *     methods={"PUT"},
     *     defaults={
     *          "__controller"="App\Controller\ApprenantController::setStudent",
     *          "__api_resource_class"=Apprenant::class,
     *          "__api_collection_operation_name"="set_student"
     *     }
     * )
     */
    public function setStudent(User $student,EntityManagerInterface $manager,Request $request,UserRepository $userRepository,SerializerInterface $serializer,ValidatorInterface $validator,UserPasswordEncoderInterface $encoder)
    {
        if($student->getRoles()[0] == "ROLE_APPRENANT"){
            return $student;
        }
        return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);

    }


}
