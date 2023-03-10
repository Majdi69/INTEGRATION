<?php

namespace App\Repository;

use App\Entity\BanqueDeSang;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BanqueDeSang>
 *
 * @method BanqueDeSang|null find($id, $lockMode = null, $lockVersion = null)
 * @method BanqueDeSang|null findOneBy(array $criteria, array $orderBy = null)
 * @method BanqueDeSang[]    findAll()
 * @method BanqueDeSang[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BanqueDeSangRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BanqueDeSang::class);
    }

    public function save(BanqueDeSang $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(BanqueDeSang $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return BanqueDeSang[] Returns an array of BanqueDeSang objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?BanqueDeSang
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
