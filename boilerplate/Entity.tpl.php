<?= "<?php\n" ?>

namespace App\Entity;

use App\Repository\<?= $singular['pascal_case'] ?>Repository;
use Doctrine\ORM\Mapping as ORM;
<?php if ($is_user) { ?>
use OHMedia\SecurityBundle\Entity\User as EntityUser;
<?php } else {
use OHMedia\SecurityBundle\Entity\Entity;
<?php } ?>

#[ORM\Entity(repositoryClass: <?= $singular['pascal_case'] ?>Repository::class)]
<?php if $is_user { ?>
class <?= $singular['pascal_case'] ?> extends EntityUser
<?php } else {
class <?= $singular['pascal_case'] ?> extends Entity
<?php } ?>
{
}
