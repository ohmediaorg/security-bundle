<?= "<?php\n" ?>

namespace App\Security\Voter;

use App\Entity\<?= $singular['pascal_case'] ?>;
<?php if (!$is_user) { ?>
use App\Entity\User;
<?php } ?>
use OHMedia\SecurityBundle\Security\Voter\EntityVoter;

class <?= $singular['pascal_case'] ?>Voter extends EntityVoter
{
    const INDEX = '<?= $singular['snake_case'] ?>_index';
    const CREATE = '<?= $singular['snake_case'] ?>_create';
    const VIEW = '<?= $singular['snake_case'] ?>_view';
    const EDIT = '<?= $singular['snake_case'] ?>_edit';
    const DELETE = '<?= $singular['snake_case'] ?>_delete';

    public function getAttributes(): array
    {
        return [
            self::INDEX,
            self::CREATE,
            self::VIEW,
            self::EDIT,
            self::DELETE,
        ];
    }

    public function getEntityClass(): string
    {
        return <?= $singular['pascal_case'] ?>::class;
    }

    protected function can<?= $singular['pascal_case'] ?>Index(User $loggedIn): bool
    {
        return true;
    }

    protected function can<?= $singular['pascal_case'] ?>Create(<?= $singular['pascal_case'] ?> $<?= $singular['camel_case'] ?>, User $loggedIn): bool
    {
        return true;
    }

    protected function can<?= $singular['pascal_case'] ?>View(<?= $singular['pascal_case'] ?> $<?= $singular['camel_case'] ?>, User $loggedIn): bool
    {
        return true;
    }

    protected function can<?= $singular['pascal_case'] ?>Edit(<?= $singular['pascal_case'] ?> $<?= $singular['camel_case'] ?>, User $loggedIn): bool
    {
        return true;
    }

    protected function can<?= $singular['pascal_case'] ?>Delete(<?= $singular['pascal_case'] ?> $<?= $singular['camel_case'] ?>, User $loggedIn): bool
    {
        return true;
    }
}
