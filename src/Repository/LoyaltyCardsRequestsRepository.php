<?php

namespace App\Repository;

use App\Entity\LoyaltyCardsRequests;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LoyaltyCardsRequests|null find($id, $lockMode = null, $lockVersion = null)
 * @method LoyaltyCardsRequests|null findOneBy(array $criteria, array $orderBy = null)
 * @method LoyaltyCardsRequests[]    findAll()
 * @method LoyaltyCardsRequests[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LoyaltyCardsRequestsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LoyaltyCardsRequests::class);
    }

    // /**
    //  * @return LoyaltyCardsRequests[] Returns an array of LoyaltyCardsRequests objects
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
    public function findOneBySomeField($value): ?LoyaltyCardsRequests
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
