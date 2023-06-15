<?= "<?php\n" ?>

namespace App\Entity;

use App\Repository\<?= $singular['pascal_case'] ?>Repository;
use Doctrine\ORM\Mapping as ORM;
use OHMedia\SecurityBundle\Entity\Traits\Blameable;
<?php if ($is_user) { ?>
use OHMedia\SecurityBundle\Entity\User as EntityUser;
<?php } ?>

#[ORM\Entity(repositoryClass: <?= $singular['pascal_case'] ?>Repository::class)]
<?php if ($is_user) { ?>
class <?= $singular['pascal_case'] ?> extends EntityUser
<?php } else { ?>
class <?= $singular['pascal_case'] ?>
<?php } ?>
{
    use Blameable;

    #[ORM\Id()]
    #[ORM\GeneratedValue()]
    #[ORM\Column(type: 'integer')]
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}
