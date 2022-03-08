<?php

namespace App\Repository;

use App\Entity\__PASCALCASE__;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @method __PASCALCASE__|null find($id, $lockMode = null, $lockVersion = null)
 * @method __PASCALCASE__|null findOneBy(array $criteria, array $orderBy = null)
 * @method __PASCALCASE__[]    findAll()
 * @method __PASCALCASE__[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class __PASCALCASE__Repository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, __PASCALCASE__::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $__CAMELCASE__, string $newHashedPassword): void
    {
        if (!$__CAMELCASE__ instanceof __PASCALCASE__) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($__CAMELCASE__)));
        }

        $__CAMELCASE__->setPassword($newHashedPassword);
        $this->_em->persist($__CAMELCASE__);
        $this->_em->flush();
    }
}
