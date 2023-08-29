<?php echo "<?php\n"; ?>

namespace App\Security\Voter;

use App\Entity\<?php echo $singular['pascal_case']; ?>;
use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Security\Voter\AbstractEntityVoter;

class <?php echo $singular['pascal_case']; ?>Voter extends AbstractEntityVoter
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
        return <?php echo $singular['pascal_case']; ?>::class;
    }

    protected function canIndex(<?php echo $singular['pascal_case']; ?> $<?php echo $singular['camel_case']; ?>, User $loggedIn): bool
    {
        return true;
    }

    protected function canCreate(<?php echo $singular['pascal_case']; ?> $<?php echo $singular['camel_case']; ?>, User $loggedIn): bool
    {
        return true;
    }
<?php if ($has_view_route) { ?>

    protected function canView(<?php echo $singular['pascal_case']; ?> $<?php echo $singular['camel_case']; ?>, User $loggedIn): bool
    {
        return true;
    }
<?php } ?>

    protected function canEdit(<?php echo $singular['pascal_case']; ?> $<?php echo $singular['camel_case']; ?>, User $loggedIn): bool
    {
        return true;
    }

    protected function canDelete(<?php echo $singular['pascal_case']; ?> $<?php echo $singular['camel_case']; ?>, User $loggedIn): bool
    {
        return true;
    }
}
