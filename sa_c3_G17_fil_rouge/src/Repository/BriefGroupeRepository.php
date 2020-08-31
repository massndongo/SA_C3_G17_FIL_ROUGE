<?php

namespace App\Repository;

use App\Entity\BriefGroupe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BriefGroupe|null find($id, $lockMode = null, $lockVersion = null)
 * @method BriefGroupe|null findOneBy(array $criteria, array $orderBy = null)
 * @method BriefGroupe[]    findAll()
 * @method BriefGroupe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BriefGroupeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BriefGroupe::class);
    }

    // /**
    //  * @return BriefGroupe[] Returns an array of BriefGroupe objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BriefGroupe
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
