<?php

namespace App\DataFixtures;

use App\Entity\Admin;
use App\Entity\Apprenant;
use App\Entity\CM;
use App\Entity\Formateur;
use App\Entity\Profil;
use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        $profils = [
            "ADMIN",
            "APPRENANT",
            "FORMATEUR",
            "CM"
        ];
        $size = \count($profils);
        $roles = [];
        for ($i=0; $i < $size; $i++) {
            $profil = new Profil();
            $profil->setLibelle($profils[$i]);
            $manager->persist($profil);
            $roles[] = $profil;
        }
        for ($i=0 ; $i < 10 ; $i++ ) {
            # code...
            $user = new User();
            $hash = $this->encoder->encodePassword($user, "admin");
            $profil = $roles[rand(0,$size-1)];
            $user->setUsername($faker->firstName)
                ->setPassword($hash)
                ->setProfil($profil);
            $profil->addUser($user);
            $manager->persist($user);
        }
        // $manager->persist($role);
        $manager->flush();
    }
}
