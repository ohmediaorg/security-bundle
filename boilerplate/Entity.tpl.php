<?= "<?php\n" ?>

namespace App\Entity;

use App\Repository\<?= $singular['pascal_case'] ?>Repository;
use Doctrine\ORM\Mapping as ORM;
use OHMedia\SecurityBundle\Entity\Traits\BlameableTrait;

#[ORM\Entity(repositoryClass: <?= $singular['pascal_case'] ?>Repository::class)]
class <?= $singular['pascal_case'] . "\n" ?>
{
    use BlameableTrait;

    #[ORM\Id()]
    #[ORM\GeneratedValue()]
    #[ORM\Column(type: 'integer')]
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return '<?= $singular['readable'] ?> #' . $this->id;
    }
}
