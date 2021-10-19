<?php

namespace App\Repository;

use App\Entity\ContactService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ContactService|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContactService|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContactService[]    findAll()
 * @method ContactService[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContactServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContactService::class);
    }

    // /**
    //  * @return ContactService[] Returns an array of ContactService objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ContactService
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
