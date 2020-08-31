<?php

namespace App\Repository;

use App\Entity\NiveauLivrablePartiel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NiveauLivrablePartiel|null find($id, $lockMode = null, $lockVersion = null)
 * @method NiveauLivrablePartiel|null findOneBy(array $criteria, array $orderBy = null)
 * @method NiveauLivrablePartiel[]    findAll()
 * @method NiveauLivrablePartiel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NiveauLivrablePartielRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NiveauLivrablePartiel::class);
    }

    // /**
    //  * @return NiveauLivrablePartiel[] Returns an array of NiveauLivrablePartiel objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?NiveauLivrablePartiel
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
