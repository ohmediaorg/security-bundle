<?php echo "<?php\n"; ?>

namespace App\Repository;

use App\Entity\<?php echo $singular['pascal_case']; ?>;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method <?php echo $singular['pascal_case']; ?>|null find($id, $lockMode = null, $lockVersion = null)
 * @method <?php echo $singular['pascal_case']; ?>|null findOneBy(array $criteria, array $orderBy = null)
 * @method <?php echo $singular['pascal_case']; ?>[]    findAll()
 * @method <?php echo $singular['pascal_case']; ?>[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class <?php echo $singular['pascal_case']; ?>Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, <?php echo $singular['pascal_case']; ?>::class);
    }

    public function save(<?php echo $singular['pascal_case']; ?> $<?php echo $singular['camel_case']; ?>, bool $flush = false): void
    {
        $this->getEntityManager()->persist($<?php echo $singular['camel_case']; ?>);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(<?php echo $singular['pascal_case']; ?> $<?php echo $singular['camel_case']; ?>, bool $flush = false): void
    {
        $this->getEntityManager()->remove($<?php echo $singular['camel_case']; ?>);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
