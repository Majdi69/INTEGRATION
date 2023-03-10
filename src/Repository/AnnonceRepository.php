<?php

namespace App\Repository;

use App\Entity\Annonce;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Twilio\Rest\Client;

/**
 * @extends ServiceEntityRepository<Annonce>
 *
 * @method Annonce|null find($id, $lockMode = null, $lockVersion = null)
 * @method Annonce|null findOneBy(array $criteria, array $orderBy = null)
 * @method Annonce[]    findAll()
 * @method Annonce[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnnonceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Annonce::class);
    }

    public function save(Annonce $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Annonce $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //tri +recherche
    public function SortBytitreAnnonce(){
        return $this->createQueryBuilder('e')
            ->orderBy('e.titre','ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function SortBydescriptionAnnonce()
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.descreption','ASC')
            ->getQuery()
            ->getResult()
            ;
    }






    public function findBydescriptionAnnonce( $descreption)
    {
        return $this-> createQueryBuilder('e')
            ->andWhere('e.descreption LIKE :descreption')
            ->setParameter('descreption','%' .$descreption. '%')
            ->getQuery()
            ->execute();
    }
    public function findBytitreAnnonce( $titre)
    {
        return $this-> createQueryBuilder('e')
            ->andWhere('e.titre LIKE :titre')
            ->setParameter('titre','%' .$titre. '%')
            ->getQuery()
            ->execute();
    }

    //SMS

    public function sms(){
        // Your Account SID and Auth Token from twilio.com/console
        $sid = 'ACd7316a9c80d20818ebe259901e2e7a04';
        $auth_token = '2ecc7e73ea9f8c7f98a80d92ed0d73b8';

        // In production, these should be environment variables. E.g.:
        // $auth_token = $_ENV["TWILIO_AUTH_TOKEN"]
        // A Twilio number you own with SMS capabilities
        $twilio_number = "+12762849300";

        $client = new Client($sid, $auth_token);
        $client->messages->create(
        // the number you'd like to send the message to
            '+21624447441',
            [
                // A Twilio phone number you purchased at twilio.com/console
                'from' => '+12762901593',
                // the body of the text message you'd like to send
                'body' => 'Une annonce a été ajoutée'
            ]
        );
    }
    public function searchAnnonce($titre) {
        $qb=  $this->createQueryBuilder('s')
            ->where('s.titre LIKE :x')
            ->setParameter('x',$titre);
        return $qb->getQuery()
            ->getResult();
    }

//    /**
//     * @return Annonce[] Returns an array of Annonce objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Annonce
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
