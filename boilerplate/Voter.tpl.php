<?= "<?php\n" ?>

namespace App\Security\Voter;

use App\Entity\<?= $singular['pascal_case'] ?>;
<?php if (!$is_user) { ?>
use App\Entity\User;
<?php } ?>
use App\Provider\<?= $singular['pascal_case'] ?>Provider;
use OHMedia\SecurityBundle\Security\Voter\EntityVoter;

class <?= $singular['pascal_case'] ?>Voter extends EntityVoter
{
    const CREATE = 'create';
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';

    protected function getAttributes(): array
    {
        return [
            self::CREATE,
            self::VIEW,
            self::EDIT,
            self::DELETE,
        ];
    }

    protected function getEntityClass(): string
    {
        return <?= $singular['pascal_case'] ?>::class;
    }

    protected function canCreate(<?= $singular['pascal_case'] ?> $<?= $singular['camel_case'] ?>, User $loggedIn): bool
    {
        return true;
    }

    protected function canView(<?= $singular['pascal_case'] ?> $<?= $singular['camel_case'] ?>, User $loggedIn): bool
    {
        return true;
    }

    protected function canEdit(<?= $singular['pascal_case'] ?> $<?= $singular['camel_case'] ?>, User $loggedIn): bool
    {
        return true;
    }

    protected function canDelete(<?= $singular['pascal_case'] ?> $<?= $singular['camel_case'] ?>, User $loggedIn): bool
    {
        return true;
    }
}
