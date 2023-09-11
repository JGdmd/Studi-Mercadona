<?php

namespace App\Repository;

use App\Entity\Promotion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Promotion>
 *
 * @method Promotion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Promotion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Promotion[]    findAll()
 * @method Promotion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PromotionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Promotion::class);
    }

    public function save(Promotion $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Promotion $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findDiscountAvailable(string $date)
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.begins <= :date')
            // orWhere permettra d'obtenir l'ensemble des promotions (même celle à venir)
            ->andWhere('p.ends >= :date')
            ->setParameter('date', $date);

        return $qb->getQuery()->getResult();
    }

    public function validateDates($id, $dateBegins, $dateEnds)
    {
        // Validation des dates
        // On vérifie que la date n'est pas dans une autre période de promotions
        $existingPromotions = $this->createQueryBuilder('p')
            ->andWhere('p.product = :productId')
            ->andWhere(':begins BETWEEN p.begins AND p.ends OR :ends BETWEEN p.begins AND p.ends OR p.begins BETWEEN :begins AND :ends')
            ->setParameter('productId', $id)
            ->setParameter('begins', $dateBegins)
            ->setParameter('ends', $dateEnds)
            ->getQuery()
            ->getResult();

        return $existingPromotions;
    }

    public function discountAvailable($id)
    {
        // On cherche la promotion actuelle
        $availableDiscount = $this->createQueryBuilder('p')
            ->andWhere('p.product = :productId')
            ->andWhere(':date BETWEEN p.begins AND p.ends')
            ->setParameter('productId', $id)
            ->setParameter('date', Date('Y-m-d'))
            ->getQuery()
            ->getResult();

        return $availableDiscount;
    }

//    /**
//     * @return Promotion[] Returns an array of Promotion objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Promotion
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
