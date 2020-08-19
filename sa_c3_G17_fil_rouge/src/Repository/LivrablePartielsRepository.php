<?php

namespace App\Repository;

use App\Entity\LivrablePartiels;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LivrablePartiels|null find($id, $lockMode = null, $lockVersion = null)
 * @method LivrablePartiels|null findOneBy(array $criteria, array $orderBy = null)
 * @method LivrablePartiels[]    findAll()
 * @method LivrablePartiels[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LivrablePartielsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LivrablePartiels::class);
    }

    // /**
    //  * @return LivrablePartiels[] Returns an array of LivrablePartiels objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LivrablePartiels
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
