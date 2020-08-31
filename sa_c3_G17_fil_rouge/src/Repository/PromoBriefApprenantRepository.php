<?php

namespace App\Repository;

use App\Entity\PromoBriefApprenant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PromoBriefApprenant|null find($id, $lockMode = null, $lockVersion = null)
 * @method PromoBriefApprenant|null findOneBy(array $criteria, array $orderBy = null)
 * @method PromoBriefApprenant[]    findAll()
 * @method PromoBriefApprenant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PromoBriefApprenantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PromoBriefApprenant::class);
    }

    // /**
    //  * @return PromoBriefApprenant[] Returns an array of PromoBriefApprenant objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PromoBriefApprenant
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
