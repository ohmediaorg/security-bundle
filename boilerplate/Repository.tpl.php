<?= "<?php\n" ?>

namespace App\Repository;

use App\Entity\<?= $singular['pascal_case'] ?>;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method <?= $singular['pascal_case'] ?>|null find($id, $lockMode = null, $lockVersion = null)
 * @method <?= $singular['pascal_case'] ?>|null findOneBy(array $criteria, array $orderBy = null)
 * @method <?= $singular['pascal_case'] ?>[]    findAll()
 * @method <?= $singular['pascal_case'] ?>[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class <?= $singular['pascal_case'] ?>Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, <?= $singular['pascal_case'] ?>::class);
    }

    public function save(<?= $singular['pascal_case'] ?> $<?= $singular['camel_case'] ?>, bool $flush = false): void
    {
        $this->getEntityManager()->persist($<?= $singular['camel_case'] ?>);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(<?= $singular['pascal_case'] ?> $<?= $singular['camel_case'] ?>, bool $flush = false): void
    {
        $this->getEntityManager()->remove($<?= $singular['camel_case'] ?>);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
