<?php

namespace App\Repository;

use App\Entity\Taux;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Taux|null find($id, $lockMode = null, $lockVersion = null)
 * @method Taux|null findOneBy(array $criteria, array $orderBy = null)
 * @method Taux[]    findAll()
 * @method Taux[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TauxRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Taux::class);
    }

    // /**
    //  * @return Taux[] Returns an array of Taux objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Taux
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
