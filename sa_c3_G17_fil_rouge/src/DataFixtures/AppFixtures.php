<?php

namespace App\DataFixtures;

use App\Entity\Admin;
use App\Entity\Apprenant;
use App\Entity\CM;
use App\Entity\Formateur;
use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        $role =new Role();
        $role->setLibelle('ROLE_CM');
        for ($f=0; $f<5; $f++)
        {
            $cm = new CM();
            $cm->setPrenom($faker->firstName())
                ->setNom($faker->lastName())
                ->setPassword('cm')
                ->setEmail($faker->email)
                ->setLogin('cm')
                ->setIsDeleted(false);

            $role->addUser($cm);


            $manager->persist($cm);
        }

        $manager->persist($role);
        $manager->flush();
    }
}
