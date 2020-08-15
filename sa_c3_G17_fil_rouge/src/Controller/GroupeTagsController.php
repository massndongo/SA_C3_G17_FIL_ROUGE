<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Entity\GroupeTag;
use App\Repository\TagRepository;
use App\Repository\GroupeTagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use ApiPlatform\Core\Validator\ValidatorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GroupeTagsController extends AbstractController
{
    /**
     * @Route(
     *     path="/api/admin/grptags",
     *     methods={"GET"},
     *     name="getGroupeTags"
     * )
     */
    public function getGroupeTags(GroupeTagRepository $groupeTagRepository)
    {
        $groupeTags = $groupeTagRepository->findBy([
            "isDeleted" => false
        ]);
        return $this->json($groupeTags,Response::HTTP_OK);
    }

    /**
     * @Route(
     *     path="/api/admin/grptags/{id<\d+>}/tags",
     *     methods={"GET"},
     *     name="getTagsInGroupeTag"
     * )
     */
    public function getTagsInGroupeTag($id,GroupeTagRepository $groupeTagRepository)
    {
        $groupeTag = $groupeTagRepository->findOneBy([
            "id" => $id
        ]);
        if($groupeTag){
            if (!$groupeTag->getIsDeleted()){
                $tags = $groupeTag->getTags();
                return $this->json($tags,Response::HTTP_OK);
            }
        }
        return $this->json(["message" => "Ressource inexistante"],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/admin/grptags/tags",
     *     methods={"GET"},
     *     name="getTags"
     * )
     */
    public function getTags(GroupeTagRepository $groupeTagRepository)
    {
        $groupeTag = $groupeTagRepository->findBy([
            "isDeleted" => false
        ]);
        $tags = [];
        $size = count($groupeTag);
        for ($i = 0;$i < $size; $i++){
//            if(!$groupeCompetence[$i]->getIsDeleted()){
                $tag = $groupeTag[$i]->getTags();
                $length = count($tag);
                for ($j = 0; $j < $length; $j++){
                    $skill = $tag[$j];
                    if(!$skill->getIsDeleted()){
                        $tags[] = $skill;
                    }
                }
//            }
        }
        return $this->json($tags,Response::HTTP_OK);
    }
    
    /**
     * @Route(
     *     path="/api/admin/grptags",
     *     methods={"POST"},
     *     name="addGroupeTag"
     * )
     */
    public function addGroupeTag(TagRepository $tagRepository,Request $request,EntityManagerInterface $manager,SerializerInterface $serializer,ValidatorInterface $validator)
    {
        $groupeTagJson = $request->getContent();
        $groupeTagTab = $serializer->decode($groupeTagJson,"json");
        $tags = $groupeTagTab["tags"];
        $groupeTagTab["tags"] = [];
        $groupeTagObj = $serializer->denormalize($groupeTagTab,"App\Entity\GroupeTag");
        $groupeTagObj->setIsDeleted(false);
        $groupeTagObj = $this->addTagToGroupe($tags,$serializer,$validator,$groupeTagObj,$manager,$tagRepository);
        $errors = (array)$validator->validate($groupeTagObj);
        if(count($errors))
            return $this->json($errors,Response::HTTP_BAD_REQUEST);
        if (!count($tags))
            return $this->json(["message" => "Ajoutez au moins un tag à cet groupe de tags."],Response::HTTP_BAD_REQUEST);
        $manager->persist($groupeTagObj);
        $manager->flush();
        return $this->json($groupeTagObj,Response::HTTP_CREATED);
    }

    /**
     * @Route(
     *     path="/api/admin/grptags/{id<\d+>}",
     *     methods={"GET"},
     *     name="getGroupeTag"
     * )
     */
    public function getGroupeTag($id,GroupeTagRepository $groupeTagRepository)
    {
        $groupeTag = $groupeTagRepository->findOneBy([
            "id" => $id
        ]);
        if($groupeTag){
            if (!$groupeTag->getIsDeleted())
                return $this->json($groupeTag,Response::HTTP_OK);
        }
        return $this->json(["message" => "Ressource inexistante"],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/admin/grptags/{id<\d+>}",
     *     methods={"PUT"},
     *     name="setGroupeTag"
     * )
     */
    public function setGroupeTag($id,EntityManagerInterface $manager,GroupeTagRepository $groupeTagRepository,TagRepository $tagRepository,Request $request,SerializerInterface $serializer,ValidatorInterface $validator)
    {
        $groupeTagJson = $request->getContent();
        $groupeTagTab = $serializer->decode($groupeTagJson,"json");
        $tags = $groupeTagTab["tags"];
        $groupeTagTab["tags"] = [];
        $groupeTagObj = $serializer->denormalize($groupeTagTab,"App\Entity\GroupeTag");
        $groupeTag = $groupeTagRepository->findOneBy([
            "id" => $id
        ]);
        $groupeTagObj->setId((int)$id)
            ->SetIsDeleted(false);
        if($groupeTag)
        {
            if(!$groupeTag->getIsDeleted())
            {
                $groupeTagObj = $this->addTagToGroupe($tags,$serializer,$validator,$groupeTagObj,$manager,$tagRepository);
                if($groupeTag != $groupeTagObj){
                    $tags = $groupeTag->getTagss();
                    $groupeTag = $this->removeTag($groupeTag,$tags);
                    $tagsObj = $groupeTagObj->getTags();
                    $groupeTag = $this->addTag($groupeTag,$tagsObj);
                    $groupeTag->setLibelle($groupeTagObj->getLibelle());
                    $manager->flush();
                }
                return $this->json($groupeTag,Response::HTTP_OK);
            }
        }
        return $this->json(["message" => "Ressource inexistante"],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/admin/grptags/{id<\d+>}",
     *     methods={"DELETE"},
     *     name="delGroupeTag"
     * )
     */
    public function delGroupeTag($id,GroupeTagRepository $groupeTagRepository,EntityManagerInterface $manager)
    {
        $groupeTag = $groupeTagRepository->findOneBy([
            "id" => $id
        ]);
        if ($groupeTag){
            if(!$groupeTag->getIsDeleted()){
                $groupeTag->setIsDeleted(true);
                $manager->flush();
                return $this->json($groupeTag,Response::HTTP_OK);
            }
        }
        return $this->json(["message" => "Ressource inexistante"],Response::HTTP_NOT_FOUND);
    }
    
    private function removeCompetence(GroupeTag $groupeTag,$tags)
    {
        foreach ($tags as $tag){
            $groupeTag->removeTag($tag);
        }
        return $groupeTag;
    }

    private function addTag(GroupeTag $groupeTag,$tags)
    {
        foreach ($tags as $tag){
            $groupeTag->addCompetence($tag);
        }
        return $groupeTag;
    }

    private function addTagToGroupe($tags,$serializer,$validator,$groupeTagObj,$manager,$tagRepository)
    {
        foreach ($tags as $tag){
            $skill = $serializer->denormalize($tag,"App\Entity\Tag");
            $id = isset($tag["id"]) ? (int)$tag["id"] : null;
            if($id)
            {
                $skill = $tagRepository->findOneBy([
                    "id" => $id
                ]);
                if(!$skill)
                    return $this->json(["message" => "Le Tag avec l'id : $id, n'existe pas."],Response::HTTP_NOT_FOUND);
                $groupeTagObj->addTag($skill);
            }else{
                $skill->setId($id);
                $skill->setIsDeleted(false);
                $error = (array) $validator->validate($skill);
                if (count($error))
                    return $this->json($error,Response::HTTP_BAD_REQUEST);
                $manager->persist($skill);
                $groupeTagObj->addTag($skill);
            }
        }
        return $groupeTagObj;
    }
}
