<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Repository\TagRepository;
use App\Repository\GroupeTagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TagsController extends AbstractController
{
    /**
     * @Route(
     *     path="/api/admin/tags/{id<\d+>}",
     *     methods={"GET"},
     *     name="getTag"
     * )
     */
    public function getTag($id,TagRepository $tagRepository)
    {
        $tag = $tagRepository->findOneBy([
            "id" => $id
        ]);
        if ($tag){
            if (!$tag->getIsDeleted())
                return $this->json($tag,Response::HTTP_OK);
        }
        return $this->json(["message" => "Ressource inexistante"],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/admin/tags",
     *     methods={"GET"},
     *     name="getTags"
     * )
     */
    public function getTags(TagRepository $tagRepository)
    {
        $tags = $tagRepository->findBy([
            "isDeleted" => false
        ]);
        return $this->json($tags,Response::HTTP_OK);
    }

    /**
     * @Route(
     *     path="/api/admin/competences",
     *     methods={"POST"},
     *     name="addCompetence"
     * )
     */
    public function addTag(Request $request,EntityManagerInterface $manager,SerializerInterface $serializer,ValidatorInterface  $validator,GroupeTagRepository $groupeTagRepository)
    {
        $tagJson = $request->getContent();
        $tagTab = $serializer->decode($tagJson,"json");
        $groupeTags = isset($tagTab["groupeTags"]) ? $tagTab["groupeTags"] : [];
        $tagTab["groupeTags"] = [];
        $tagObj = $serializer->denormalize($tagTab,"App\Entity\Tag");
        $tagObj->setIsDeleted(false);
        $errors = $validator->validate($tagObj);
        if (count($errors)){
            return $this->json($errors,Response::HTTP_BAD_REQUEST);
        }
        if(!count($tagTab)){
            return $this->json(["message" => "Ajouter au moins un groupe de tags"],Response::HTTP_BAD_REQUEST);
        }
        $tagObj = $this->addGroupeToTag($groupeTags,$groupeTagRepository,$serializer,$validator,$tagObj,$manager);
            if(count($error))
            {
                return $this->json($error,Response::HTTP_BAD_REQUEST);
            }
        $manager->persist($tagObj);
        $manager->flush();
        return $this->json($tagObj,Response::HTTP_CREATED);
    }
    
    /**
     * @Route(
     *     path="/api/admin/competences/{id<\d+>}",
     *     methods={"PUT"},
     *     name="setTag"
     * )
     */
    public function setCompetence($id,TagRepository $tagRepository,Request $request,SerializerInterface $serializer,EntityManagerInterface $manager,ValidatorInterface $validator,GroupeTagRepository $groupeTagRepository)
    {
        $tag = $tagRepository->findOneBy([
            "id" => $id
        ]);
        if(!$tag || $tag->getIsDeleted())
            return  $this->json(["message" => "Ressource inexistante."],Response::HTTP_NOT_FOUND);
        $tagJson = $request->getContent();
        $tagTab = $serializer->decode($tagJson,"json");
        $groupeTag = isset($tagTab["groupeTags"]) ? $tagTab["groupeTags"] : [];
        $tagTab["groupeTags"] = [];
        $tagObj = $serializer->denormalize($tagTab,"App\Entity\Tag");
        $errors = $validator->validate($tagObj);
        if(count($errors))
            return $this->json($errors,Response::HTTP_BAD_REQUEST);
        if(!count($groupeTag) || !isset($groupeTag[0]["id"]))
            return $this->json(["message" => "Le groupe de competence est obligatoire."],Response::HTTP_BAD_REQUEST);
        $idGrpeTag = (int) $groupeTag[0]["id"];
        $oldGroupeTagObj = $groupeTagRepository->findOneBy([
            "id" => $idGrpeTag
        ]);
        if(!$oldGroupeTagObj || $oldGroupeTagObj->getIsDeleted())
            return $this->json(["message" => "Ressource inexistante."],Response::HTTP_NOT_FOUND);
        $tagObj->addGroupeTag($oldGroupeTagObj);
        $tag->setLibelle($tagObj->getLibelle())
                    ->setDescriptif($tagObj->getDescriptif());
        $manager->flush();
        return $this->json($tag,Response::HTTP_OK);
    }


    private function addGroupeToTag($groupeTags,$groupeTagRepository,$serializer,$validator,$tagObj,$manager)
    {
        foreach ($groupeTags as $groupeTag)
        {
            $id = isset($groupeTag["id"]) ? $groupeTag["id"] : null;
            if ($id)
            {
                $groupe = $groupeTagRepository->findOneBy([
                    "id" => $id
                ]);
                if(!$groupe || $groupe->getIsDeleted())
                    return $this->json(["message" => "Ressource inexistante"],Response::HTTP_NOT_FOUND);
                $tagObj->addGroupeTag($groupe);
            }
        }
        return $tagObj;
    }
}
