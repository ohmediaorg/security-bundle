<?= "<?php\n" ?>

namespace App\Security\Voter;

use App\Entity\<?= $singular['pascal_case'] ?>;
use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Security\Voter\EntityVoter;

class <?= $singular['pascal_case'] ?>Voter extends EntityVoter
{
    public const INDEX = 'index';
    public const CREATE = 'create';
<?php if ($has_view_route) { ?>
    public const VIEW = 'view';
<?php } ?>
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    protected function getAttributes(): array
    {
        return [
            self::INDEX,
            self::CREATE,
<?php if ($has_view_route) { ?>
            self::VIEW,
<?php } ?>
            self::EDIT,
            self::DELETE,
        ];
    }

    protected function getEntityClass(): string
    {
        return <?= $singular['pascal_case'] ?>::class;
    }

    protected function canIndex(<?= $singular['pascal_case'] ?> $<?= $singular['camel_case'] ?>, User $loggedIn): bool
    {
        return true;
    }

    protected function canCreate(<?= $singular['pascal_case'] ?> $<?= $singular['camel_case'] ?>, User $loggedIn): bool
    {
        return true;
    }
<?php if ($has_view_route) { ?>

    protected function canView(<?= $singular['pascal_case'] ?> $<?= $singular['camel_case'] ?>, User $loggedIn): bool
    {
        return true;
    }
<?php } ?>

    protected function canEdit(<?= $singular['pascal_case'] ?> $<?= $singular['camel_case'] ?>, User $loggedIn): bool
    {
        return true;
    }

    protected function canDelete(<?= $singular['pascal_case'] ?> $<?= $singular['camel_case'] ?>, User $loggedIn): bool
    {
        return true;
    }
}
