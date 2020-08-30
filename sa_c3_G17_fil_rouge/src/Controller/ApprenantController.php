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
        RESOURCE_NOT_FOUND = "Ressource inexistante.",
        APPRENANT_READ = "apprenant:read";

    private $apprenantRepository,
            $serializer;

    public function __construct(ApprenantRepository $apprenantRepository,SerializerInterface $serializer)
    {
        $this->apprenantRepository = $apprenantRepository;
        $this->serializer = $serializer;
    }
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
        if (!$this->isGranted("VIEW",new Apprenant()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $students = $apprenantRepository->findBy([
            "isDeleted" => false
        ]);
        $students = $this->serializer->normalize($students,null,["groups" => [self::APPRENANT_READ]]);
        return $this->json($students,Response::HTTP_OK);
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
    public function addStudents(Request $request,UserPasswordEncoderInterface $encoder,EntityManagerInterface $manager,ValidatorInterface $validator,ProfilRepository $profilRepository)
    {
        if (!$this->isGranted("ADD",new Apprenant()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $opened = false;
        $profil = $profilRepository->findOneBy(["libelle" => "APPRENANT"]);
        $student = $request->getContent();
        $avatar = $request->files->get("avatar");
        if($avatar){
            $avatar = fopen($avatar->getRealPath(),"rb");
            $student["avatar"] = $avatar;
            $opened = true;
        }
        $student = $this->serializer>denormalize($student,"App\Entity\Apprenant");
        $errors = $validator->validate($student);
        if (count($errors)){
            $errors = $this->serializer>serialize($errors,"json");
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
        $student = $this->serializer->normalize($student,null,["groups" => [self::APPRENANT_READ]]);
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
    public function getStudent($id)
    {
        if (!$this->isGranted("VIEW",new Apprenant()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $student = $this->apprenantRepository->findOneBy(["id" => $id]);
        if ($student && !$student->getIsDeleted())
        {
            $student = $this->serializer->normalize($student,null,["groups" => [self::APPRENANT_READ]]);
            return $this->json($student,Response::HTTP_OK);
        }
        return $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
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
    public function setStudent($id)
    {
        if (!$this->isGranted("EDIT",new Apprenant()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $student = $this->apprenantRepository->findOneBy(["id" => $id]);
        if ($student && $student->getIsDeleted())
        {
            $student = $this->serializer->normalize($student,null,["groups" => [self::APPRENANT_READ]]);
            return $this->json($student,Response::HTTP_OK);
        }
        return $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);

    }


}
