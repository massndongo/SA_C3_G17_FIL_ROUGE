<?php

namespace App\Controller;

use App\Entity\Brief;
use App\Entity\Livrables;
use App\Entity\PromoBrief;
use App\Entity\LivrableAttendu;
use App\Repository\TagRepository;
use App\Entity\PromoBriefApprenant;
use App\Repository\BriefRepository;
use App\Repository\NiveauRepository;
use App\Repository\FormateurRepository;
use App\Repository\GroupesRepository;
use App\Repository\ApprenantRepository;
use App\Repository\LivrablesRepository;
use App\Repository\RessourceRepository;
use App\Repository\PromoBriefRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReferentielRepository;
use App\Repository\LivrableAttenduRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\PromosRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PromoBriefApprenantRepository;
use ApiPlatform\Core\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BriefController extends AbstractController
{
    private $briefRepository,
        $promosRepository,
        $groupesRepository,
        $connectedTeacher,
        $serializer,
        $em,
        $security;
    private const ACCESS_DENIED = "Vous n'avez pas accés à cette ressource.",
        RESOURCE_NOT_FOUND = "Ressource inexistante.",
        BRIEF_READ = "getBriefs:read";

    public function __construct(BriefRepository $briefRepository, 
                                PromosRepository $promosRepository,
                                GroupesRepository $groupesRepository, 
                                TokenStorageInterface $tokenStorage,
                                SerializerInterface $serializer, 
                                EntityManagerInterface $em, 
                                ValidatorInterface $validator)
    {
        $this->briefRepository = $briefRepository;
        $this->promosRepository = $promosRepository;
        $this->groupesRepository = $groupesRepository;
        $this->connectedTeacher = $tokenStorage->getToken()->getUser();
        $this->em = $em;
        $this->validator = $validator;
        $this->serializer = $serializer;
    }

    /**
     * @Route(
     *     path="/api/formateurs/briefs",
     *     methods={"GET"},
     *     name="getBriefs",
     *     defaults={
     *          "_controller"="\App\Controller\BriefController::getBriefs",
     *          "_api_resource_class"=Brief::class,
     *          "_api_collection_operation_name"="getBriefs",
     *          "_api_receive"=false
     *      }
     * )
    */
    public function getBriefs()
    {
        if (!$this->isGranted("VIEW",new Brief()))
        {
            return $this->json(["message" => self::ACCESS_DENIED]);
        }
        $briefs = $this->briefRepository->findAll();
        $briefs = $this->serializer->normalize($briefs,null,["groups" => [self::BRIEF_READ]]);
        return $this->briefRepository->findAll();
    }

    /**
     * @Route(
     *     path="/api/formateurs/promos/{idPromo<\d+>}/briefs/{idBrief<\d+>}",
     *     methods={"GET"},
     *     name="getBriefInPromo",
     *     defaults={
     *          "_controller"="\App\Controller\BriefController::getBriefInPromo",
     *          "_api_resource_class"=Brief::class,
     *          "_api_collection_operation_name"="getBriefInPromo"
     *      }
     * )
     */
    public function getBriefInPromo($idPromo,$idBrief)
    {
        if (!$this->isGranted("VIEW",new Brief()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $promo = $this->promosRepository->findOneBy(["id" => (int)$idPromo]);
        if($promo && !$promo->getIsDeleted())
        {
            $brief = $this->briefRepository->findOneBy(["id" => (int)$idBrief]);
            if ($brief)
            {
                $promoBriefs = $brief->getPromoBriefs();
                foreach ($promoBriefs as $promoBrief)
                {
                    if ($promoBrief->getBrief() == $brief)
                    {
                        return $brief;
                    }
                }
            }
        }
        return  $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/formateurs/promos/{id}/briefs",
     *     methods={"GET"},
     *     name="getBbriefsFormateur",
     *     defaults={
     *          "_controller"="\App\Controller\BriefController::getBbriefsFormateur",
     *          "_api_resource_class"=Brief::class,
     *          "_api_collection_operation_name"="getBriefsFormateur"
     *      }
     * )
     */
    public function getBbriefsFormateur($id)
    {
        if (!$this->isGranted("VIEW",new Brief()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $promo = $this->promosRepository->findOneBy(["id" => (int)$id]);
        if($promo && !$promo->getIsDeleted())
        {
            $promoBriefs = $promo->getPromoBriefs();
            $briefs = [];
            foreach ($promoBriefs as $promoBrief)
            {
                $briefs[] = $promoBrief->getBrief();
            }
            return $briefs;
        }
        return  $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/formateurs/promos/{idPromo}/groupes/{idGroupe}/briefs",
     *     methods={"GET"},
     *     name="getBriefsInGroupe",
     *     defaults={
     *          "_controller"="\App\Controller\BriefController::getBriefsInGroupe",
     *          "_api_resource_class"=Brief::class,
     *          "_api_collection_operation_name"="getBriefsInGroupe"
     *      }
     * )
     */
    public function getBriefsInGroupe($idPromo,$idGroupe)
    {
        if(!$this->isGranted("VIEW",new Brief()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $promo = $this->promosRepository->findOneBy(["id" => (int)$idPromo]);
        if ($promo && !$promo->getIsDeleted())
        {
            $groupe = $this->groupesRepository->findOneBy(["id" => (int)$idGroupe]);
            $message = '';
            $status = null;
            if ($groupe)
            {
                $groupesInPromo = $promo->getGroupes()->getValues();
                if (in_array($groupe,$groupesInPromo))
                {
                    return $groupe->getBriefs();
                }
                else{
                    $message = "Ce groupe n'est pas dans cette promo.";
                    $status = Response::HTTP_NOT_FOUND;
                }
            }
            else{
                $message = "Ce groupe est introuvable .";
                $status = Response::HTTP_NOT_FOUND;
            }
            return $this->json(["message" => $message],$status);
        }
        return  $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/formateurs/{id<\d+>}/briefs/brouillons",
     *     methods={"GET"},
     *     name="getBriefsBrouillonFormateur",
     *     defaults={
     *          "_controller"="\App\Controller\BriefController::getBriefsBrouillonFormateur",
     *          "_api_resource_class"=Brief::class,
     *          "_api_collection_operation_name"="getBriefsBrouillonFormateur"
     *      }
     * )
     */
    public function getBriefsBrouillonFormateur($id,FormateurRepository $formateurRepository)
    {
        if (!$this->isGranted("VIEW",new Brief()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $formateur = $formateurRepository->findOneBy(["id" => (int)$id]);
        if($formateur && !$formateur->getIsDeleted())
        {
            $result = $this->filterBriefWithStatus($formateur,"brouillon");
            return $result["message"];
        }
        return  $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/formateurs/{id<\d+>}/briefs/valide",
     *     methods={"GET"},
     *     name="getFormateurValideBriefs",
     *     defaults={
     *          "_controller"="\App\Controller\BriefController::getBriefsBrouillonFormateur",
     *          "_api_resource_class"=Brief::class,
     *          "_api_collection_operation_name"="getBriefsBrouillonFormateur"
     *      }
     * )
     */
    public function getFormateurValideBriefs($id,FormateurRepository $formateurRepository)
    {
        if (!$this->isGranted("VIEW",new Brief()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $formateur = $formateurRepository->findOneBy(["id" => (int)$id]);
        if ($formateur && !$formateur->getIsDeleted())
        {
            $result = $this->filterBriefWithStatus($formateur,"valide");
            return $result["message"];
        }
        return  $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }

    private function filterBriefWithStatus($formateur,$statusBrief)
    {
        $briefsValides = [];
        $briefs = $formateur->getBriefs();
        foreach ($briefs as $brief)
        {
            if ($brief->getStatutBrief() == $statusBrief)
            {
                $briefsValides[] = $brief;
            }
        }
        return ["message" =>$briefsValides,"status" =>Response::HTTP_OK];
    }

    /**
     * @Route(
     *     path="/api/apprenants/promos/{id<\d+>}/briefs",
     *     methods={"GET"},
     *     name="getBriefsApprenant",
     *     defaults={
     *          "_controller"="\App\Controller\BriefController::getBriefsApprenant",
     *          "_api_resource_class"=Brief::class,
     *          "_api_collection_operation_name"="getBriefsApprenant"
     *      }
     * )
     */
    public function getBriefsApprenant($id)
    {
        if(!$this->isGranted("VIEW",new Brief()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $promo = $this->promosRepository->findOneBy(["id" => (int)$id]);
        if ($promo && !$promo->getIsDeleted())
        {
            $briefAssigneApprenants = [];
            $promoBriefs = $promo->getPromoBriefs();
            foreach ($promoBriefs as $promoBrief)
            {
                $brief = $promoBrief->getBrief();
                if($brief->getStatutBrief() == "assigne")
                {
                    $briefAssigneApprenants[] = $brief;
                }
            }
            return $briefAssigneApprenants;
        }
        return  $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *     path="/api/apprenants/promos/{idPromo<\d+>}/briefs/{idBrief<\d+>}",
     *     methods={"GET"},
     *     name="getLivrableRenduApprenant",
     *     defaults={
     *          "_api_resource_class"=Brief::class,
     *          "_api_collection_operation_name"="getLivrableRenduApprenant",
     *          "_controller"="App\Controller\BriefController::getLivrableRenduApprenant",
     *          "_api_receive"=false,
     *     }
     * )
     */
    public function getLivrableRenduApprenant($idPromo,$idBrief,PromoBriefRepository $promoBriefRepository)
    {
        if(!$this->isGranted("VIEW",new Brief()))
        {
            return $this->json(["message" => self::ACCESS_DENIED],Response::HTTP_FORBIDDEN);
        }
        $promo = $this->promosRepository->findOneBy(["id" => $idPromo]);
        if($promo && !$promo->getIsDeleted())
        {
            $brief = $this->briefRepository->findOneBy(["id" => $idBrief]);
            if($brief)
            {
                $promoBrief = $promoBriefRepository->findOneBy(["promo" => $promo,"brief" => $brief]);
                if($promoBrief)
                {
                    return $brief;
                }
            }
        }
        return  $this->json(["message" => self::RESOURCE_NOT_FOUND],Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route(
     *      name="duplcate_brief",
     *      path="api/formateurs/briefs/{id}",
     *      methods={"POST"},
     *      defaults={
     *          "_controller"="\app\BriefController::duplcateBrief",
     *          "_api_resource_class"=Brief::class,
     *          "_api_collection_operation"="duplcate_brief"
     *      }
     * )
     */
    public function duplcateBrief(BriefRepository $repo, int $id,TokenStorageInterface $tokenStorage) {
        $newBrief = new Brief();
        $brief = $repo->findOneBy(["id" => $id]);
        if($brief && !$brief->getIsDeleted()){
            $newBrief = clone $brief;
            $newBrief->setId(null)
                    ->setStatutBrief("Brouillon");
            foreach ($newBrief->getGroupes() as $groupe) {
                $newBrief->removeGroupe($groupe);
            }
            foreach ($newBrief->getNiveaux() as $niveau) {
                $newBrief->removeNiveau($niveau);
            }
            foreach ($newBrief->getRessources() as $ressource) {
                $newBrief->removeRessource($ressource);
            }
            foreach ($newBrief->getLivrableAttendus() as $livAttendu) {
                foreach ($livAttendu->getLivrables() as $livrable) {
                    $livAttendu->removeLivrable($livrable);
                }
            }
            foreach ($newBrief->getPromoBriefs() as $promoBrief) {
                $newBrief->removePromoBrief($promoBrief);
            }
            $errors = $this->validator->validate($newBrief);
            $errors = (array)$errors;
            if (count($errors)){
                $errors = $this->serializer->serialize($errors,"json");
                return new JsonResponse($errors,Response::HTTP_BAD_REQUEST,[],true);
            }
            $this->em->persist($newBrief);
            $this->em->flush();
            return $this->json(["message" => "Brief dupliqué"], Response::HTTP_CREATED);
        }else{
            return $this->json(["message" => "Resource Not found"], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @Route(
     *      name="add_brief",
     *      path="api/formateurs/briefs",
     *      methods={"POST"},
     *      defaults={
     *          "_controller"="\app\BriefController::addBrief",
     *          "_api_resource_class"=Brief::class,
     *          "_api_collection_operation"="add_brief"
     *      }
     * )
     */
    public function addBrief(Request $request,TagRepository $repoTag,TokenStorageInterface $tokenStorage,NiveauRepository $repoNiveau,ReferentielRepository $repoRef,GroupesRepository $repoGrpe,\Swift_Mailer $mailer) {
        $brief = new Brief();
        $briefTab = $request->request->all();        
        $avatar = $request->files->get("avatar");
        $avatar = fopen($avatar->getRealPath(),"rb");
        $briefTab["avatar"] = $avatar;
        $brief->setLangue($briefTab["langue"])
            ->setTitre($briefTab["titre"])
            ->setDescription($briefTab["description"])
            ->setContexte($briefTab["contexte"])
            ->setLivrable($briefTab["livrable"])
            ->setModalitesPedagogiques($briefTab["modalitesPedagogiques"])
            ->setCritereDePerformance($briefTab["critereDePerformance"])
            ->setModalitesEvaluation($briefTab["modalitesEvaluation"])
            ->setIsDeleted(false)
            ->setDateCreation(new \DateTime());
        if(isset($briefTab["tags"]) && !empty($briefTab["tags"])){
            foreach($briefTab["tags"] as $idTag){
                $tag = $repoTag->find($idTag);
                if($tag){
                    $brief->addTag($tag);
                }else {
                    return $this->json(["message" => "Le tag est obligatoire"], Response::HTTP_BAD_REQUEST);
                }
            }
        }
        if(isset($briefTab["niveaux"]) && !empty($briefTab["niveaux"])){
            foreach($briefTab["niveaux"] as $idNiveau){
                $idNiveau = (int)$idNiveau;
                $niveau = $repoNiveau->findOneBy(["id" => $idNiveau]);
                if($niveau){
                    $brief->addNiveau($niveau);
                }else {
                    return $this->json(["message" => "Le niveau est obligatoire"], Response::HTTP_BAD_REQUEST);
                }
            }
        }
        if(isset($briefTab["livrableAttendus"]) && !empty($briefTab["livrableAttendus"])){
            foreach($briefTab["livrableAttendus"] as $lv){
                $livrableAtt = new LivrableAttendu();
                $livrableAtt->setLibelle($lv["libelle"]);
                $brief->addLivrableAttendu($livrableAtt);
            }
        }
        if(isset($briefTab["referentiel"]) && !empty($briefTab["referentiel"])){
            $refe = (int)$briefTab["referentiel"];
            $ref = $repoRef->find($refe);
            if($ref){
                $brief->setReferentiel($ref);
            }else{
                return $this->json(["message" => "Le referentiel est obligatoire"], Response::HTTP_BAD_REQUEST);
            }
        }else{
            return $this->json(["message" => "Le referentiel est obligatoire"], Response::HTTP_BAD_REQUEST);
        }
        if(isset($briefTab['promoBriefs'])){
            $promoBrief=new PromoBrief();
            $promoBrief->setStatut($briefTab['promoBriefs']);
            $promoBrief->setIsDeleted(false);
            if(isset($briefTab['groupes'])){
                $tabGroup = explode("/",$briefTab['groupes']);
                foreach ($tabGroup as $group){
                    $group = (int)$group;
                    $groups=  $repoGrpe->find($group);
                }
                $promoBrief->setPromo($groups->getPromos());
                $brief->addPromoBrief($promoBrief);
            }

        }

        if(isset($briefTab["groupes"])){
            $tabGroup = explode("/",$briefTab['groupes']);
            foreach($tabGroup as $grpe){
                $grpe = (int)$grpe;
                $groupe = $repoGrpe->findOneBy(["id" => $grpe]);
                if ($groupe) {
                    $brief->setStatutBrief("assigne");
                    foreach($groupe->getApprenant() as $apprenant){
                        $promoBriefApprenant=new PromoBriefApprenant();
                        $promoBriefApprenant->setStatut("assigne");
                        $promoBriefApprenant->setApprenant($apprenant);
                        $promoBriefApprenant->setPrommoBrief($promoBrief);
                        $dataFormateur=$brief->getFormateur();
                        $message = (new \Swift_Message('Bonjour '.$apprenant->getPrenom().' '.$apprenant->getNom().'Un nouveau brief vous a été assigné'))
                            ->setFrom('massndongo86@gmail.com')
                            ->setTo($apprenant->getEmail());
                        $mailer->send($message);
                        $this->em->persist($promoBriefApprenant);
                        $this->em->flush();
                    }
                    $brief->addGroupe($groupe);
                }
                else {
                    $brief->setStatutBrief("Brouillon");
                }
            }
        }else{
            $brief->setStatutBrief("Valide");
        }
        $creator = $tokenStorage->getToken()->getUser();
        $brief->setFormateur($creator);
        $errors = (array)$this->validator->validate($brief);
        if (count($errors)){
            return $this->json($errors,Response::HTTP_BAD_REQUEST);
        }
        $this->em->persist($brief);
        $this->em->flush();
        fclose($avatar);
        return $this->json(["message" => "Brief créé"], Response::HTTP_CREATED);
    }
    /**
     * @Route(
     *      name="add_assignation",
     *      path="api/formateurs/promo/{idPromo}/brief/{idBrief}/assignation",
     *      methods={"PUT"},
     *      defaults={
     *          "_controller"="\app\BriefController::addAssignation",
     *          "_api_resource_class"=Brief::class,
     *          "_api_item_operation"="add_assignation"
     *      }
     * )
     */
    public function assignerBrief($idPromo,$idBrief,PromoBriefApprenantRepository $promoBriefApprenantRepository, PromoBriefRepository $promoBriefRepo ,GroupesRepository $grpRepo,Request $request,BriefRepository $brfRepo,PromosRepository $promoRepo,\Swift_Mailer $mailer){
        $data=json_decode($request->getContent(),true);
        $promo=$promoRepo->find($idPromo);
        $brief=$brfRepo->find($idBrief);
        if(isset($data['groupes'])){
            foreach ($data['groupes'] as $groupeId){
                if($groupeId['action']!="assigner" && $groupeId['action']!="desaffecter") {
                    return $this->json(["message" => "Veuillez mettre l'action assigner ou desaffacter"], Response::HTTP_CREATED);
                }
                $groupe=$grpRepo->find($groupeId['id']);
                if ($groupeId['action']=="desaffecter") {
                    if($groupe->getApprenant()[0]->getPromoBriefApprenants()){
                        $skill = false;
                        foreach($groupe->getApprenant()[0]->getPromoBriefApprenants() as $ets){
                            if($ets->getPromobrief()->getBrief()->getId()==$idBrief && $ets->getPromobrief()->getPromo()->getId()==$idPromo){
                                $myPromoBrief=$ets->getPromobrief()->getId();
                                foreach ($groupe->getApprenant() as $deleteAppre){
                                    $prep= $promoBriefApprenantRepository->findBy(["apprenant"=>$deleteAppre->getId(),"promobrief"=>$myPromoBrief]);
                                    if($prep){
                                        //Groupe désaffecter
                                        $this->em->remove($prep[0]);
                                        $this->em->flush();
                                        return $this->json(["message" => "Brief Desaffecter"]);
                                    }
                                }
                            }
                        }
                    }
                }if ($groupeId['action']=="assigner") {
                    if($groupe->getApprenant()[0]->getPromoBriefApprenants()){
                        foreach($groupe->getApprenant()[0]->getPromoBriefApprenants() as $ets){
                            if(!($ets->getPromobrief()->getBrief()->getId()==$idBrief && $ets->getPromobrief()->getPromo()->getId()==$idPromo)){
                                $addPromo=$promoRepo->find( $groupe->getPromos()->getId());
                                $promoBrief=new PromoBrief();
                                $promoBrief->setPromo($addPromo);
                                $promoBrief->setBrief($brief);
                                $promoBrief->setStatut('Enours');
                                $promoBrief->setIsDeleted(false);
                                $groupe->addBrief($brief);
                                foreach ($groupe->getApprenant() as $myApprenants){
                                    $prombrfappr=new PromoBriefApprenant();
                                    $prombrfappr->setPromobrief($promoBrief);
                                    $prombrfappr->setStatut("assigner");
                                    $myApprenants->addPromoBriefApprenant($prombrfappr);
                                    $donneesFormateur=$brief->getFormateur()->getPrenom()." ".$brief->getFormateur()->getNom();
                                    $message = (new \Swift_Message('Bonjour '.$myApprenants->getPrenom().' '.$myApprenants->getNom().' Un nouveau brief vous a été assigné'))
                                        ->setFrom('massndongo86@gmail.com')
                                        ->setTo($myApprenants->getEmail());
                                    $mailer->send($message);
                                }
                            } 
                        }
                    }
                }
            }
        }
        $this->em->flush();
        return new JsonResponse("ok",Response::HTTP_OK,[],true);
    }
    /**
     * @Route(
     *      name="set_brief",
     *      path="api/formateurs/promo/{idP}/brief/{idB}",
     *      methods={"PUT"},
     *      defaults={
     *          "_controller"="\app\BriefController::setBrief",
     *          "_api_resource_class"=Brief::class,
     *          "_api_item_operation"="set_brief"
     *      }
     * )
     */
    public function setBrief(LivrableAttenduRepository $livrablesAttendusRepository,TokenStorageInterface $token,RessourceRepository $ressRepo,TagRepository $tagRepo,NiveauRepository $nivRepo,PromoBriefRepository $promoBriefRepo,Request $request,$idP,$idB){
        //dd($token->getToken()->getUser());
        $data=$request->request->all();
        $promoUpdate = $promoBriefRepo->findBy(["promo"=>$idP,"brief"=>$idB]);
        //dd($data);
        /*$ressourceAdd= $request->files->get("ressource");
        if($ressourceAdd){
            $ressourceAdd = fopen($ressourceAdd->getRealPath(),"rb");
            $ressourceAddind=new Ressource();
            $ressourceAddind->setIsDelete(false);
            $ressourceAddind->setPieceJointe($ressourceAdd);
            $ressourceAddind->setTitre("Piece jointe");
        }*/
        if($promoUpdate){
           $promo=$promoUpdate[0]->getPromo();
           if(isset($data['archivage'])){
               $promoUpdate[0]->getBrief()->setIsDelete(true);
           }
           if(isset($data['ressource'])){
               $ressource=$ressRepo->find($data['ressource']);
               $ressource->setIsDelete(false);
               $promoUpdate[0]->getBrief()->removeRessource($ressource);
           }
           if(isset($data['statut'])){
               $promoUpdate[0]->setStatut("cloture");
           }
           if(isset($data['statutBrief'])){
               $promoUpdate[0]->getBrief()->setStatutBrief($data['statutBrief']);
           }
           if(isset($data['tag'])){
               $tabTags=explode("/",$data['tag']);
               if(count($promoUpdate[0]->getBrief()->getTag())>0){
                   foreach ($tabTags as $tagId){
                       $skill=false;
                       foreach ( $promoUpdate[0]->getBrief()->getTag() as $myTag){
                           if($myTag->getId()==$tagId){
                               $skill=true;
                           }
                       }
                       $thisTag=$tagRepo->find($tagId);
                       if($skill==true){
                           if($thisTag) {
                               $promoUpdate[0]->getBrief()->removeTag($thisTag);
                           }
                       }else{
                           if($thisTag) {
                               $promoUpdate[0]->getBrief()->addTag($thisTag);
                           }
                       }
                   }
               }else{
                   foreach ($tabTags as $tagId){
                       $thisTag=$tagRepo->find($tagId);
                       $promoUpdate[0]->getBrief()->addTag($thisTag);
                   }
               }
            }
           if(isset($data['livrablesAttendus'])) {
               $tabLivrables=explode("/",$data['livrablesAttendus']);
                if(count($promoUpdate[0]->getBrief()->getLivrableAttendus())>0){
                    foreach ($tabLivrables as $val){
                        $skill=false;
                        if(preg_match("#[0-9]#", $val['0'])){
                            foreach ($promoUpdate[0]->getBrief()->getLivrableAttendus() as $mesLivr){
                                if($mesLivr->getId()==(int)$val){
                                    $skill=true;
                                }
                            }
                            $thisLivrable=$livrablesAttendusRepository->find($val);
                            if($skill==true){
                                if($thisLivrable)
                                {
                                    $promoUpdate[0]->getBrief()->removeLivrableAttendu($thisLivrable);
                                }
                            }else{
                                if($thisLivrable)
                                {
                                    $promoUpdate[0]->getBrief()->addLivrableAttendu($thisLivrable);
                                }
                            }
                        }else{
                            $livrable=new LivrableAttendu();
                            $livrable->setIsDelete(true);
                            $livrable->setLibelle($val);
                            $promoUpdate[0]->getBrief()->addLivrableAttendu($livrable);
                        }
                    }
                }else{
                    foreach ($tabLivrables as $val){
                      $thisLivrable=$livrablesAttendusRepository->find($val);
                      if($thisLivrable){
                          $promoUpdate[0]->getBrief()->addLivrableAttendu($thisLivrable);
                      }
                    }
                }
            }
            if(isset($data['niveaux'])){
               $tabNiveaux=explode("/",$data['niveaux']);
               if(count($promoUpdate[0]->getBrief()->getNiveaux() )>0){
                    foreach ($tabNiveaux as $niveauId){
                        $skill=false;
                        foreach ($promoUpdate[0]->getBrief()->getNiveaux() as $myNiveau){
                            if($myNiveau->getId()==$niveauId){
                                $skill=true;
                            }
                        }
                        $thisNiveau=$nivRepo->find($niveauId);
                        if($skill==true){
                            if($thisNiveau) {
                                $promoUpdate[0]->getBrief()->removeNiveau($thisNiveau);
                            }
                        }else{
                            if($thisNiveau) {
                                $promoUpdate[0]->getBrief()->addNiveau($thisNiveau);
                            }
                        }
                    }
                }else{
                   foreach ($tabNiveaux as $niveauId){
                       $thisNiveau=$nivRepo->find($niveauId);
                       $promoUpdate[0]->getBrief()->addNiveau($thisNiveau);
                   }
               }
           }
       }
       /*if($ressourceAdd)
        {
            fclose($ressourceAdd);
        }*/
       $this->em->persist($promoUpdate[0]);
       $this->em->flush();
       return $this->json(["message" => "Brief Modifier"],Response::HTTP_OK);
    }
     /**
     * @Route(
     *     name="add_url_livrables_attendus",
     *     path="api/apprenants/{idStudent}/groupe/{idGroupe}/livrables",
     *     methods={"POST"},
     *     defaults={
     *          "__controller"="App\Controller\BriefController::addUrlLivrablesAttendus",
     *          "__api_resource_class"=Brief::class,
     *          "__api_collection_operation_name"="add_url_livrables_attendus"
     *     }
     * )
     */
    public function addUrlLivrablesAttendus(Request $request,ApprenantRepository $apprenantRepository,GroupesRepository $groupeRepository,LivrablesRepository $repoLivrable, LivrableAttenduRepository $repoLivrableAttendu,$idStudent,$idGroupe)   {
        $data = json_decode($request->getContent(), true);
        //dd($data);
        $id = $data["id"];
        $libelle = $data["libelle"];
        $url = $data["url"];   
        //dd((string)$url); 
        $apprenant = $apprenantRepository->find($idStudent);
        if (empty($apprenant)) {
            return new JsonResponse("Cet apprenant n'est pas repertorié sur le système.", Response::HTTP_NOT_FOUND, [], true);
        }
        $groupe = $groupeRepository->find($idGroupe);
        if (empty($groupe)) {
            return new JsonResponse("Ce groupe n'existe pas.", Response::HTTP_NOT_FOUND, [], true);
        }
        $trouve = false;
        $apprenants = $groupe->getApprenant();
        foreach ($apprenants as $value) {
            if ($value == $apprenant) {
                $trouve = true;
                break;
            }
        }
        if (!$trouve) {
            return new JsonResponse("Cet apprenant n'appartient pas à ce groupe.", Response::HTTP_NOT_FOUND, [], true);
        }
        if($groupe->getType()==="principal"){
            $liv = $repoLivrable->findBy(["livrableAttendu"=>$id, "apprenant"=>$idStudent]);
            if($liv){
                $liv[0]->setUrl($data["url"]);
                $this->em->persist($liv[0]);
            }else{
                $newLiv = new Livrables();
                $newLiv->setUrl($url);
                $newLiv->setApprenant($apprenant);
                $livAtt = $repoLivrableAttendu->findBy(["id"=>$id]);
                if ($livAtt) {
                    $newLiv->setLivrableAttendu($livAtt[0]);
                }else{
                    $livrableAttendu = new LivrableAttendu();
                    $livrableAttendu->setLibelle($libelle);
                    $newLiv->setLivrableAttendu($livrableAttendu);
                }
                $this->em->persist($newLiv);
            }
        }
        if($groupe->getType()==="secondaire"){
            $liv = $repoLivrable->findBy(["livrableAttendu"=>$id, "apprenant"=>$idStudent]);
            foreach ($apprenants as $app) {
                if($liv){
                    $l = $repoLivrable->findBy(["livrableAttendu"=>$id]);
                    if ($l) {
                        foreach ($l as $value) {
                            $value->setUrl($url);
                        }
                    } 
                }else{
                    $newLiv = new Livrables();
                    $newLiv->setUrl($url);
                    $newLiv->setApprenant($app);
                    $livAtt = $repoLivrableAttendu->findBy(["id"=>$id]);
                    if ($livAtt) {
                        $newLiv->setLivrableAttendu($livAtt[0]);
                    }else{
                        $livrableAttendu = new LivrableAttendu();
                        $livrableAttendu->setLibelle($libelle);
                        $newLiv->setLivrableAttendu($livrableAttendu);
                    }
                    $this->em->persist($newLiv);
                }
            }
        }
        $this->em->flush();

        return new JsonResponse("Livrables enregistrés avec succès.", Response::HTTP_CREATED, [], true);
    }
}