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
       // $faker = Factory::create('fr_FR');

        $role =new Profil();
        $role->setLibelle('ADMIN');
        $user = new User();
        $hash = $this->encoder->encodePassword($user, "admin");
        $user->setUsername('admin')
           ->setPassword($hash)
           ->setProfil($role);



        $manager->persist($role);
        $manager->persist($user);
        $manager->flush();
    }
}
