<?php

namespace App\Controller;

use App\Entity\Brief;
use App\Entity\PromoBrief;
use App\Repository\TagRepository;
use App\Entity\PromoBriefApprenant;
use App\Repository\BriefRepository;
use App\Repository\NiveauRepository;
use App\Repository\PromosRepository;
use App\Repository\GroupesRepository;
use App\Repository\PromoBriefRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReferentielRepository;
use Symfony\Component\HttpFoundation\Request;
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
    private $serializer;
    private $em;
    private $security;

    public function __construct(SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator)
    {
        $this->serializer = $serializer;
        $this->em = $em;
        $this->validator = $validator;
    }

    /**
     * @Route(
     *      name="duplcate_brief",
     *      path="api/formateurs/briefs/{id}",
     *      methods="POST",
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
     *      methods="POST",
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
     *      methods="PUT",
     *      defaults={
     *          "_controller"="\app\BriefController::addAssignation",
     *          "_api_resource_class"=Brief::class,
     *          "_api_collection_operation"="add_assignation"
     *      }
     * )
     */
    public function assignerBrief($idPromo,$idBrief,PromoBriefApprenantRepository $promoBriefApprenantRepository, PromoBriefRepository $promoBriefRepo ,GroupesRepository $grpRepo,Request $request,BriefRepository $brfRepo,PromosRepository $promoRepo,\Swift_Mailer $mailer){
        
        $data=json_decode($request->getContent(),true);
        $promo=$promoRepo->find($idPromo);
        $brief=$brfRepo->find($idBrief);
        if(isset($data['apprenants'])){
            $groupes=$promo->getGroupes();
            foreach ($groupes as $key){
                if($key->getType()=='principal'){
                    $groupePrincipal=$key;
                break;
                }
                else {
                    return $this->json(["message" => "Les apprenants ne font pas de ce promo. Changer les id d'apprenants"]);
                }    
            }
            
            $apprenants=$groupePrincipal->getApprenant();
            foreach ($data['apprenants'] as $appAModif){
                foreach ($apprenants as $apprenant){
                    if($apprenant->getId()==$appAModif['id']){
                        $skill=false;
                        if(isset($apprenant->getPromoBriefApprenants()[0])  ){
                            foreach($apprenant->getPromoBriefApprenants() as $keyss){
                                if($keyss->getPromobrief()->getBrief()){
                                    if($keyss->getPromobrief()->getBrief()->getId()==$brief->getId()){
                                        $skill=true;

                                    }
                                }
                            }
                            if($skill==true){
                                foreach ($apprenant->getPromoBriefApprenants() as $toDelete){
                                    if($toDelete->getPromobrief()->getPromo()->getId() ==$promo->getId()){
                                        $prep= $promoBriefApprenantRepository->findBy(["apprenant"=>$apprenant->getId(),"promobrief"=>$keyss->getPromobrief()->getId()]);
                                        if($prep){
                                            $this->em->remove($prep[0]);
                                            $this->em->flush();
                                        }
                                    }

                                }
                            }else{
                                $aAjouter=$promoBriefRepo->findBy(["promo"=>$idPromo,"brief"=>$idBrief]);
                                $c=new PromoBriefApprenant();
                                $c->setStatut("assigne");
                                if(isset($aAjouter[0]))
                                {
                                    $c->setPromobrief($aAjouter[0]);
                                }else{
                                    $newPromBrief=new PromoBrief();
                                    $newPromBrief->setIsDelete(true);
                                    $newPromBrief->setStatut('en cours');
                                    $newPromBrief->setBrief($brief);
                                    $newPromBrief->setPromo($promo);
                                    $c->setPromobrief($newPromBrief);

                                }
                                $apprenant->addPromoBriefApprenant($c);
                                $donneesFormateur=$brief->getFormateur()->getPrenom()." ".$brief->getFormateur()->getNom();
                                $message = (new \Swift_Message('Bonjour '.$apprenant->getPrenom().' '.$apprenant->getNom().' Un nouveau brief vous a été assigné'))
                                    ->setFrom('massndongo86@gmail.com')
                                    ->setTo($apprenant->getEmail());
                                $mailer->send($message);
                                $this->em->persist($c);
                                $this->em->flush();
                            }
                    }else{

                            $aAjouter=$promoBriefRepo->findBy(["promo"=>$idPromo,"brief"=>$idBrief]);

                            $c=new PromoBriefApprenant();
                            $c->setStatut("assigne");
                            $c->setPromobrief($aAjouter[0]);
                            $apprenant->addPromoBriefApprenant($c);
                            $this->em->persist($c);
                            $this->em->flush();
                        }

                    }
                }
            }

        }
        if(isset($data['groupes'])){
            foreach ($data['groupes'] as $groupeId){

                $groupe=$grpRepo->find($groupeId['id']);
                    if($groupe->getApprenant()[0]->getPromoBriefApprenants()){
                        $skill=false;
                        foreach($groupe->getApprenant()[0]->getPromoBriefApprenants() as $ets){
                            if($ets->getPromobrief()->getBrief()->getId()==$idBrief && $ets->getPromobrief()->getPromo()->getId()==$idPromo){
                                $myPromoBrief=$ets->getPromobrief()->getId();

                                $skill=true;
                            }
                        }
                        if($skill==true){

                            foreach ($groupe->getApprenant() as $deleteAppre){
                                $prep= $promoBriefApprenantRepository->findBy(["apprenant"=>$deleteAppre->getId(),"promobrief"=>$myPromoBrief]);
                                if($prep){
                                     $this->em->remove($prep[0]);
                                    $this->em->flush();
                                }
                            }
                            //$apprenant->removePromoBriefApprenant($prep);

                        }

                    }else{
                        $skill=false;
                    }
                    if($skill==false){
                        $addPromo=$promoRepo->find( $groupe->getPromo()->getId());
                        $PromoBrief=new PromoBrief();
                        $PromoBrief->setPromo($addPromo);
                        $PromoBrief->setBrief($brief);
                        $PromoBrief->setStatut('en cours');
                        $groupe->addBrief($brief);
                        foreach ($groupe->getApprenant() as $myApprenants){
                            $prombrfappr=new PromoBriefApprenant();
                            $prombrfappr->setPromobrief($PromoBrief);
                            $myApprenants->addPromoBriefApprenant($prombrfappr);
                            //MAIL
                            $donneesFormateur=$brief->getFormateur()->getPrenom()." ".$brief->getFormateur()->getNom();
                            $message = (new \Swift_Message('Bonjour '.$myApprenants->getPrenom().' '.$myApprenants->getNom().' Un nouveau brief vous a été assigné'))
                                ->setFrom('massndongo86@gmail.com')
                                ->setTo($myApprenants->getEmail())
                                // you can remove the following code if you don't define a text version for your emails

                            ;

                            $mailer->send($message);
                            //END MAIL
                        }
                    }

             //   $em->persist($PromoBrief);
               // $em->persist($groupe);

            }
        }
        $this->em->flush();
        return new JsonResponse("ok",Response::HTTP_OK,[],true);
    }
            /**
     * @Route(
     *      name="set_brief",
     *      path="api/formateurs/promo/{idPromo}/brief/{id}",
     *      methods="PUT",
     *      defaults={
     *          "_controller"="\app\BriefController::setBrief",
     *          "_api_resource_class"=Brief::class,
     *          "_api_collection_operation"="set_brief"
     *      }
     * )
     */
    public function setBrief()
    {
        
    }
                /**
     * @Route(
     *      name="delete_brief",
     *      path="api/formateurs/promo/{idPromo}/brief/{id}",
     *      methods="DELETE",
     *      defaults={
     *          "_controller"="\app\BriefController::deleteBrief",
     *          "_api_resource_class"=Brief::class,
     *          "_api_collection_operation"="delete_brief"
     *      }
     * )
     */
    public function deleteBrief()
    {
        
    }
    /*private function assignBriefInGroupe($brief,$groupe,$repoGrpe){
        $groupe = $repoGrpe->find($grpe);
        if($groupe){
            $brief->addGroupe($groupe);
            $promoBrief = new PromoBrief();
            $promoBrief
                ->setStatut("EnCours")
                ->setPromo($groupe->getPromos())
                ->setBrief($brief);
            $this->em->persist($promoBrief);
            foreach($groupe->getApprenant() as $apprenant){
                $message = (new \Swift_Message("Vous etes assigne a ce brief"))
                    ->setFrom("massndongo86@gmail.com")
                    ->setTo($apprenant->getEmail())
                    ->setBody("Bonjour ".$apprenant->getPrenom()." ".$apprenant->getNom()."\nLe brief ". $brief->getTitre() ." vous a été assigné.\nMerci.");
                $mailer->send($message);
            }
        }
        return $brief;
    }*/
}
