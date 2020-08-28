<?php

namespace App\DataFixtures;

use App\Entity\Commentaire;
use App\Entity\LivrablePartiels;
use App\Entity\LivrableRendu;
use App\Entity\PromoBriefApprenant;
use App\Repository\ApprenantRepository;
use App\Repository\FormateurRepository;
use App\Repository\NiveauRepository;
use App\Repository\PromoBriefRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ApprenantFixtures extends Fixture implements FixtureGroupInterface
{
    private $apprenantRepository,
            $formateurRepository,
            $niveaurepository,
            $promoBriefRepository;

    public function __construct(ApprenantRepository $apprenantRepository,FormateurRepository $formateurRepository,NiveauRepository $niveauRepository,PromoBriefRepository $promoBriefRepository)
    {
        $this->apprenantRepository = $apprenantRepository;
        $this->formateurRepository = $formateurRepository;
        $this->niveaurepository = $niveauRepository;
        $this->promoBriefRepository = $promoBriefRepository;
    }
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        $apprenants = $this->apprenantRepository->findAll();
        $promoBriefs = $this->promoBriefRepository->findAll();
//        $formateurs= $this->formateurRepository->findAll();
//        $niveaux = $this->niveaurepository->findAll();
//        $statutLivrablePartiels = ["en groupe","individuel"];
//        $statutLivrableRendus = ["valide","rendu","a refaire","assigne"];
        $statutPromobriefApprenant = ["valide","rendu","non valide","assigne"];
        $times = 10;
//        for ($i = 0; $i < $times; $i++)
//        {
//            $randomStudent = random_int(0,count($apprenants)-1);
//            $randomLevel = random_int(0,count($niveaux)-1);
//            $randomTeacher = random_int(0,count($formateurs)-1);
//            $randomSatusLivrablePartiel = random_int(0,count($statutLivrablePartiels)-1);
//            $randomSatusLivrableRendu = random_int(0,count($statutLivrableRendus)-1);
//            $livrablePartiel = new LivrablePartiels();
//            $livrableRendu = new LivrableRendu();
//            $commentaire = new  Commentaire();
//            $livrablePartiel->setLibelle($faker->paragraph(1))
//                            ->setDateCreation($faker->dateTime)
//                            ->setDescription($faker->paragraph(1))
//                            ->setDelai($faker->dateTimeThisMonth)
//                            ->setType($statutLivrablePartiels[$randomSatusLivrablePartiel])
//                            ->addNiveau($niveaux[$randomLevel]);
//            $delai = $statutLivrableRendus[$randomSatusLivrableRendu] != "valide" ? $faker->dateTimeThisMonth : null;
//            $livrableRendu->setDelai($delai)
//                        ->setStatut($statutLivrableRendus[$randomSatusLivrableRendu] )
//                        ->setApprenant($apprenants[$randomStudent])
//                        ->setDateDeRendu($faker->dateTime)
//                        ->setLivrablePartiel($livrablePartiel);
//            $commentaire->setLibelle($faker->paragraph(1))
//                        ->setDate($faker->dateTime)
//                        ->setFormateur($formateurs[$randomTeacher])
//                        ->setLivrableRendu($livrableRendu);
//            $manager->persist($livrablePartiel);
//            $manager->persist($livrableRendu);
//            $manager->persist($commentaire);
//        }

        for ($i = 0; $i < $times; $i++)
        {
            $promoBriefApprenant = new PromoBriefApprenant();
            $randomStatus = random_int(0,count($statutPromobriefApprenant)-1);
            $randomStudent = random_int(0,count($apprenants)-1);
            $randomPromobrief = random_int(0,count($promoBriefs)-1);
            $promoBriefApprenant->setApprenant($apprenants[$randomStudent])
                                ->setStatut($statutPromobriefApprenant[$randomStatus])
                                ->setPromoBrief($promoBriefs[$randomPromobrief]);
            $manager->persist($promoBriefApprenant);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        // TODO: Implement getGroups() method.
        return ["group2"];
    }
}
