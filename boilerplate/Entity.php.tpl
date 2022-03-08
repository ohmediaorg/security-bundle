<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $repository_full_class_name ?>;
use Doctrine\ORM\Mapping as ORM;
use OHMedia\SecurityBundle\Entity\Entity;<?php if ($lockable): ?>
use OHMedia\SecurityBundle\Entity\Traits\Lockable;
<?php endif ?>

#[ORM\Entity(repositoryClass: <?= $repository_class_name ?>::class)]
class <?= $class_name ?> extends Entity
{<?php if ($lockable): ?>
    use Lockable;
<?php endif ?>
}
