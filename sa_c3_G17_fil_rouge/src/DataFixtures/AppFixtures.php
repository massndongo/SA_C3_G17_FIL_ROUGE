<?php

namespace App\DataFixtures;

use App\Entity\CM;
use Faker\Factory;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\Admin;
use App\Entity\Profil;
use App\Entity\Apprenant;
use App\Entity\GroupeCompetence;
use App\Entity\Competence;
use App\Entity\Formateur;
use App\Entity\Groupes;
use App\Entity\Referentiel;
use App\Repository\ProfilRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;
    //private $adminRepository;
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
      //  $this->adminRepository = $adminRepository;
    }

    public function load(ObjectManager $manager)
    {
       $faker = Factory::create();
       $tabDev=['html','php','javascript','mysql'];
       $tabGestion=['Anglais des affaires','Marketing','Community management'];


       $admin = $this->adminRepository->findAll();
        $times = 5;
        for ($i = 0; $i < $times; $i++){
            foreach ($admins as $admin){
                $prof = null;
                if($admin->getProfil == "FORMATEUR"){
                    $prof= $admin->getId();
                }
            }
        }
       $groupe= new GroupeCompetence();
                $groupe->setLibelle('Developpement Web')
                    ->setIsDeleted(false)
                    ->setAdministrateur($prof)
                    ->setDescriptif('description developpement web');

                $manager->persist($groupe);
        for ($c=0; $c<count($tabDev); $c++){

            $comp= new Competence();
            $comp->setLibelle($tabDev[$c])
                ->setDescriptif('Dev')
                ->addGroupeCompetence($groupe)
                ->setIsDeleted(false);
            $manager->persist($comp);

        }

        $groupe2= new GroupeCompetence();
                $groupe2->setLibelle('Gestion de Projet')
                ->setAdministrateur($prof)
                    ->setIsDeleted(false)
                    ->setDescriptif('description gestion de projet');
                $manager->persist($groupe2);

        for ($c=0; $c<count($tabGestion); $c++){
            $comp1= new Competence();
            $comp1->setLibelle($tabGestion[$c])
                ->setDescriptif('Gestion')
                ->setIsDeleted(false)
                ->addGroupeCompetence($groupe2);
            $manager->persist($comp1);


        }

       $manager->flush();
    /* $profils = [
            "ADMIN",
           "APPRENANT",
           "FORMATEUR"
       ];*/
      /*  $formateurs = $this->userRepository->findAll();
        $times = 5;
        for ($i = 0; $i < $times; $i++){
            foreach ($formateurs as $formateur){
                $prof = null;
                if($formateur->getProfil == "FORMATEUR"){
                    $prof= $formateur;
                }
                //$password = "";
                

                /*if ($profil->getLibelle() == "APPRENANT"){
                    $entity = new Apprenant();
                    $password = "apprenant";
                }else
                if ($profil->getLibelle() == "FORMATEUR"){
                    $entity = new Formateur();
                    $password = "formateur";
                }elseif ($profil->getLibelle()== "ADMIN"){
                    $entity = new Admin();
                    $password = "admin";
                }
                $entity->setPrenom($faker->fistName())
                    ->setNom($faker->lastName)
                    ->setPassword($this->encoder->encodePassword($entity,$password))
                    ->setEmail($faker->email)
                    ->setProfil($profil)
                    ->setUsername($faker->firstName())
                    ->setIsDeleted(false);
                $manager->persist($entity);
                //$manager->persist($role);
            }
        }
        for ($i=0; $i < 3 ; $i++) { 
            $groupe1 = new Groupes;
            $groupe1->setNom('Groupe'.($i+1))
                    ->setDateCreation(new \DateTime())
                    ->setStatut('Statut'.($i+1))
                    ->setType('Type'.($i+1));
                    $manager->persist($groupe1);
        }

        $times = 5;
         for ($i = 0; $i < $times; $i++){
                 $referentiel = new Referentiel();
                 $referentiel->setLibelle('Libelle '.$i)
                     ->setPresentation('Presentation '.$i)
                     ->setProgramme('Programme '.$i)
                     ->setCritereAdmission('CritereAdmission '.$i)
                     ->setCritereEvaluation('CritereEvaluation '.$i)
                     ->setIsDeleted(false);
                $manager->persist($referentiel);
         }
        
        $promos = new Promos();
        $promos->setLangue('Français')
                ->setTitre('Promos 3')
                ->setDescription('Description Promos 3')
                ->setLieu('Orange Digital Center')
                ->setReference('Web Mobile')
                ->setDateDebut($faker->dateTime())
                ->setDateFinProvisoire($faker->dateTime())
                ->setFabrique('Projet Quizz')
                ->setDateFinReelle($faker->dateTime())
                ->setEtat('Actif')
                ->addFormateur($prof)
                ->addGroupe($groupe1)
                ->addReferenciel($referentiel)
                ;
            $manager->persist($promos);*/
        $manager->flush();

     
 /*//        $profils = [
 //            "ADMIN",
 //            "APPRENANT",
 //            "FORMATEUR"
 //        ];
//         $profils = $this->profilRepository->findAll();
         $times = 10;
         for ($i = 0; $i < $times; $i++){
// //            $profil = new Profil();
// //            $profil->setLibelle($libelle);
                 $entity = new Referentiel();
                 $entity->setLibelle('Libelle '.$i)
                     ->setPresentation('Presentation '.$i)
                     ->setProgramme('Programme '.$i)
                     ->setCritereAdmission('CritereAdmission '.$i)
                     ->setCritereEvaluation('CritereEvaluation '.$i)
                     ->setIsDeleted(false);
                $manager->persist($entity);
         }*/
    
    }
}