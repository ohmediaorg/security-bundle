<?php echo "<?php\n"; ?>

namespace App\Entity;

use App\Repository\<?php echo $singular['pascal_case']; ?>Repository;
use Doctrine\ORM\Mapping as ORM;
use OHMedia\SecurityBundle\Entity\Traits\BlameableTrait;

#[ORM\Entity(repositoryClass: <?php echo $singular['pascal_case']; ?>Repository::class)]
class <?php echo $singular['pascal_case']."\n"; ?>
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
        return '<?php echo $singular['readable']; ?> #' . $this->id;
    }
}
