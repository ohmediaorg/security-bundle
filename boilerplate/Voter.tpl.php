<?= "<?php\n" ?>

namespace App\Security\Voter;

use App\Entity\<?= $singular['pascal_case'] ?>;
use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Security\Voter\EntityVoter;

class <?= $singular['pascal_case'] ?>Voter extends EntityVoter
{
    const ATTRIBUTE_PREFIX = '<?= $singular['snake_case'] ?>';
    const INDEX = self::ATTRIBUTE_PREFIX . 'index';
    const CREATE = self::ATTRIBUTE_PREFIX . 'create';
<?php if ($has_view_route) { ?>
    const VIEW = self::ATTRIBUTE_PREFIX . 'view';
<?php } ?>
    const EDIT = self::ATTRIBUTE_PREFIX . 'edit';
    const DELETE = self::ATTRIBUTE_PREFIX . 'delete';

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
