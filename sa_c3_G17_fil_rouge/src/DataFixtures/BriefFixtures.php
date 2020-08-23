<?php

namespace App\DataFixtures;

use App\Entity\Brief;
use App\Entity\LivrableAttendu;
use App\Entity\Livrables;
use App\Entity\PromoBrief;
use App\Entity\Ressource;
use App\Repository\ApprenantRepository;
use App\Repository\FormateurRepository;
use App\Repository\GroupesRepository;
use App\Repository\NiveauRepository;
use App\Repository\PromosRepository;
use App\Repository\ReferentielRepository;
use App\Repository\TagRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class BriefFixtures extends Fixture implements  FixtureGroupInterface
{
    private $groupesRepository,
            $formateurRepository,
            $referentielRepository,
            $niveauRepository,
            $tagRepository,
            $apprenantRepository,
            $promosRepository;

    public function __construct(GroupesRepository $groupesRepository,FormateurRepository $formateurRepository,
                                ReferentielRepository $referentielRepository,NiveauRepository $niveauRepository,
                                TagRepository $tagRepository,ApprenantRepository $apprenantRepository,
                                PromosRepository  $promosRepository)
    {
        $this->groupesRepository = $groupesRepository;
        $this->formateurRepository = $formateurRepository;
        $this->referentielRepository = $referentielRepository;
        $this->niveauRepository = $niveauRepository;
        $this->tagRepository = $tagRepository;
        $this->apprenantRepository = $apprenantRepository;
        $this->promosRepository = $promosRepository;
    }
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        $languages = ["english","french","spanish","arabic"];
        $formateurs = $this->formateurRepository->findAll();
        $niveaux = $this->niveauRepository->findAll();
        $groupes = $this->groupesRepository->findAll();
        $tags = $this->tagRepository->findAll();
        $referentiels = $this->referentielRepository->findAll();
        $apprenants = $this->apprenantRepository->findAll();
        $promos = $this->promosRepository->findAll();
        $times = 10;
        for ($i = 0; $i < $times; $i++)
        {
            $randomLanguageIndex = random_int(0,count($languages)-1);
            $randomFormateurIndex = random_int(0,count($formateurs)-1);
            $randomNiveauIndex = random_int(0,count($niveaux)-1);
            $randomGroupeIndex = random_int(0,count($groupes)-1);
            $randomTagIndex = random_int(0,count($tags)-1);
            $randomReferentielIndex = random_int(0,count($referentiels)-1);
            $randomApprenantIndex = random_int(0,count($apprenants)-1);
            $randomPromoIndex = random_int(0,count($promos)-1);
            $brief = new Brief();
            $promoBrief = new PromoBrief();
            $livrableAttendu = new LivrableAttendu();
            $livrable = new Livrables();
            $ressource = new Ressource();
            $livrableAttendu->setLibelle($faker->paragraph);
            $livrable->setUrl($faker->url)
                ->setLivrableAttendu($livrableAttendu)
                ->setApprenant($apprenants[$randomApprenantIndex]);
            $brief->setTitre($faker->jobTitle)
                ->setDescription($faker->paragraph)
                ->setLangue($languages[$randomLanguageIndex])
                ->setContexte($faker->paragraph(1))
                ->setDateCreation($faker->dateTime)
                ->setCritereDePerformance($faker->paragraph)
                ->setlivrable($faker->paragraph)
                ->setModalitesPedagogiques($faker->paragraph)
                ->setModalitesEvaluation($faker->paragraph)
                ->setStatutBrief("en cours")
                ->setFormateur($formateurs[$randomFormateurIndex])
                ->setReferentiel($referentiels[$randomReferentielIndex])
                ->addNiveau($niveaux[$randomNiveauIndex])
                ->addGroupe($groupes[$randomGroupeIndex])
                ->addTag($tags[$randomTagIndex])
                ->addLivrableAttendu($livrableAttendu);
            $promoBrief->setBrief($brief)
                ->setStatut("en cours")
                ->setPromo($promos[$randomPromoIndex]);
            $ressource->setBrief($brief)
                ->setUrl($faker->url)
                ->setTitre($faker->jobTitle);
            $manager->persist($livrableAttendu);
            $manager->persist($livrable);
            $manager->persist($ressource);
            $manager->persist($promoBrief);
            $manager->persist($brief);
        }
        $manager->flush();
    }
    public static function getGroups() : array
    {
        return ["group1"];
    }
}
