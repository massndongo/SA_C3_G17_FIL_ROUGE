<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user")
     */
    public function index()
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    /**
    * @Route(
    * name="User_liste",
    * path="/admin/users",
    * methods={"POST"},
    * defaults={
    * "_controller"="\App\UserController::addUser",
    * "_api_resource_class"=User::class,
    * "_api_collection_operation_name"="add_user"
    * }
    * )
    */
    public function addUser(Request $request)
    {
        dd($request);
        $uploadedFile = $request->files->get('file');
        if (!$uploadedFile) {
            throw new BadRequestHttpException('"file" is required');
        }
        return $uploadedFile;
    }

}
