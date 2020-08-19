<?php

namespace App\Repository;

use App\Entity\Livrables;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Livrables|null find($id, $lockMode = null, $lockVersion = null)
 * @method Livrables|null findOneBy(array $criteria, array $orderBy = null)
 * @method Livrables[]    findAll()
 * @method Livrables[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LivrablesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livrables::class);
    }

    // /**
    //  * @return Livrables[] Returns an array of Livrables objects
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
    public function findOneBySomeField($value): ?Livrables
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
