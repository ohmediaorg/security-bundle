<?= "<?php\n" ?>

namespace App\Security\Voter;

use App\Entity\<?= $singular['pascal_case'] ?>;
use OHMedia\SecurityBundle\Entity\User as EntityUser;
use OHMedia\SecurityBundle\Security\Voter\EntityVoter;

class <?= $singular['pascal_case'] ?>Voter extends EntityVoter
{
    const INDEX = '<?= $singular['snake_case'] ?>_index';
    const CREATE = '<?= $singular['snake_case'] ?>_create';
<?php if ($has_view_route) { ?>
    const VIEW = '<?= $singular['snake_case'] ?>_view';
<?php } ?>
    const EDIT = '<?= $singular['snake_case'] ?>_edit';
    const DELETE = '<?= $singular['snake_case'] ?>_delete';

    protected function getEntityClass(): string
    {
        return <?= $singular['pascal_case'] ?>::class;
    }

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

    protected function canIndex(<?= $singular['pascal_case'] ?> $<?= $singular['camel_case'] ?>, EntityUser $loggedIn): bool
    {
        return true;
    }

    protected function canCreate(<?= $singular['pascal_case'] ?> $<?= $singular['camel_case'] ?>, EntityUser $loggedIn): bool
    {
        return true;
    }
<?php if ($has_view_route) { ?>

    protected function canView(<?= $singular['pascal_case'] ?> $<?= $singular['camel_case'] ?>, EntityUser $loggedIn): bool
    {
        return true;
    }
<?php } ?>

    protected function canEdit(<?= $singular['pascal_case'] ?> $<?= $singular['camel_case'] ?>, EntityUser $loggedIn): bool
    {
        return true;
    }

    protected function canDelete(<?= $singular['pascal_case'] ?> $<?= $singular['camel_case'] ?>, EntityUser $loggedIn): bool
    {
        return true;
    }
}
