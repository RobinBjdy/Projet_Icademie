<?php

namespace App\Repository;

use App\Entity\Materiel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Materiel>
 *
 * @method Materiel|null find($id, $lockMode = null, $lockVersion = null)
 * @method Materiel|null findOneBy(array $criteria, array $orderBy = null)
 * @method Materiel[]    findAll()
 * @method Materiel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaterielRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Materiel::class);
    }

    public function add(Materiel $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Materiel $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Materiel[] Returns an array of Materiel objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Materiel
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

public function findMaterielDispo(){
    return $this->createQueryBuilder('m')
           ->andWhere("m.disponible = 1")
           ->getQuery()
           ->getResult()
       ;
}

public function findMateriels($keyword, $categorie, $dispo, $reserv){
    $qb = $this->createQueryBuilder('m');
    
    if($keyword != null){
        $qb->andWhere('m.libelle LIKE :keyword');
        $qb->setParameter('keyword', '%'.$keyword.'%');
    }

    if($categorie != null){
        $qb->andWhere('m.categorie = :categorie');
        $qb->setParameter('categorie', $categorie);
    }

    if($dispo != null){
        $qb->andWhere('m.disponible = :dispo');
        $qb->setParameter('dispo', $dispo);
    }

    if($reserv != null){
        $qb->andWhere('m.reservation = :reserv');
        $qb->setParameter('reserv', $reserv);
    }
    
    return $qb->getQuery()->getResult();
}

}
