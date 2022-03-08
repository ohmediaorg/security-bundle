<?php

namespace App\Repository;

use App\Entity\__PASCALCASE__;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method __PASCALCASE__|null find($id, $lockMode = null, $lockVersion = null)
 * @method __PASCALCASE__|null findOneBy(array $criteria, array $orderBy = null)
 * @method __PASCALCASE__[]    findAll()
 * @method __PASCALCASE__[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class __PASCALCASE__Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, __PASCALCASE__::class);
    }
}
