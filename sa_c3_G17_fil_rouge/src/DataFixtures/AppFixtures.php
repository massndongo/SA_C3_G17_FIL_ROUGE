<?php

namespace App\DataFixtures;

use App\Entity\Admin;
use App\Entity\Apprenant;
use App\Entity\CM;
use App\Entity\Formateur;
use App\Entity\Profil;
use App\Entity\Role;
use App\Entity\User;
use App\Repository\ProfilRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;
    private $profilRepository;
    public function __construct(UserPasswordEncoderInterface $encoder,ProfilRepository $profilRepository)
    {
        $this->encoder = $encoder;
        $this->profilRepository = $profilRepository;
    }

    public function load(ObjectManager $manager)
    {
       $faker = Factory::create();
//        $profils = [
//            "ADMIN",
//            "APPRENANT",
//            "FORMATEUR"
//        ];
        $profils = $this->profilRepository->findAll();
        $times = 10;
        for ($i = 0; $i < $times; $i++){
            foreach ($profils as $profil){
//            $profil = new Profil();
//            $profil->setLibelle($libelle);
                $password = "";
                $entity = null;
                if ($profil->getLibelle() == "APPRENANT"){
                    $entity = new Apprenant();
                    $password = "apprenant";
                }elseif ($profil->getLibelle() == "FORMATEUR"){
                    $entity = new Formateur();
                    $password = "formateur";
                }elseif ($profil->getLibelle()== "ADMIN"){
                    $entity = new Admin();
                    $password = "admin";
                }
                $entity->setPrenom($faker->firstName())
                    ->setNom($faker->lastName)
                    ->setPassword($this->encoder->encodePassword($entity,$password))
                    ->setEmail($faker->email)
                    ->setProfil($profil)
                    ->setUsername($faker->firstName())
                    ->setIsDeleted(false);
                $manager->persist($entity);
                $manager->persist($profil);
            }
        }
        $manager->flush();
    }
}
