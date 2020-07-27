<?php

namespace App\Controller;
use App\Entity\Apprenant;
use App\Entity\User;
use App\Repository\ApprenantRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApprenantController extends AbstractController
{
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

        $studens = $apprenantRepository->findAll();
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
    public function addStudents(Request $request,SerializerInterface $serializer,UserPasswordEncoderInterface $encoder,EntityManagerInterface $manager,ValidatorInterface $validator,UserRepository $userRepository)
    {
        $student_profil = $userRepository->findOneBy([
            "id" => 2
        ]);
        $student = $request->request->all();
        $avatar = $request->files->get("avatar");
        $avatar = fopen($avatar->getRealPath(),"rb");
        $student["avatar"] = $avatar;
        $student = $serializer->denormalize($student,"App\Entity\Apprenant");
        $errors = $validator->validate($student);
        if (count($errors)){
            $errors = $serializer->serialize($errors,"json");
            return new JsonResponse($errors,Response::HTTP_BAD_REQUEST,[],true);
        }else if($student->getProfil()->getId() != $student_profil->getId()){
            $errors = [
                "message" => "Veuillez choisir le profil apprenant"
            ];
            $errors = $serializer->serialize($errors,"json");
            return new JsonResponse($errors,Response::HTTP_BAD_REQUEST,[],true);
        }
        $password = $student->getPassword();
        $student->setPassword($encoder->encodePassword($student,$password));
        $manager->persist($student);
        $manager->flush();
        fclose($avatar);
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
    public function getStudent(Apprenant $student)
    {
        $idStudentProfil = 2;
        if($student->getProfil()->getId() == $idStudentProfil){
            return $this->json($student,Response::HTTP_OK);
        }else{
            return $this->json(["message" => "Vous n'avez pas acces à cette ressource"],Response::HTTP_FORBIDDEN);
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
    public function setStudent(Apprenant $set_student,EntityManagerInterface $manager,Request $request,UserRepository $userRepository,SerializerInterface $serializer,ValidatorInterface $validator,UserPasswordEncoderInterface $encoder)
    {
        $student_profil = $userRepository->findOneBy([
            "id" => 2
        ]);
        $student = $request->request->all();
        $avatar = $request->files->get("avatar");
        $avatar = fopen($avatar->getRealPath(),"rb");
        $student["avatar"] = $avatar;
        $student = $serializer->denormalize($student,"App\Entity\Apprenant");
        $errors = $validator->validate($student);
        if (count($errors)){
            $errors = $serializer->serialize($errors,"json");
            return new JsonResponse($errors,Response::HTTP_BAD_REQUEST,[],true);
        }else if($student->getProfil()->getId() != $student_profil->getId()){
            $errors = [
                "message" => "Veuillez choisir le profil apprenant"
            ];
            $errors = $serializer->serialize($errors,"json");
            return new JsonResponse($errors,Response::HTTP_BAD_REQUEST,[],true);
        }
        $password = $student->getPassword();
        $student->setPassword($encoder->encodePassword($student,$password));
        if($student->getPassword() != $set_student->getPassword()){
            $set_student->setPassword($student->getPassword());
        }
        if($student->getUsername() != $set_student->getUsername()){
            $set_student->setUsername($student->getUsername());
        }
        if($student->getAvatar() != $set_student->getAvatar()){
            $set_student->setAvatar($student->getAvatar());
        }
        $manager->flush();
        fclose($avatar);
        return $this->json($student,Response::HTTP_CREATED);
    }
}
