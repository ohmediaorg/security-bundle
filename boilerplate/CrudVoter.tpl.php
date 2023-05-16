<?= "<?php\n" ?>

namespace App\Security\Voter\<?= $singular['pascal_case'] ?>;

use App\Entity\<?= $singular['pascal_case'] ?>;
use OHMedia\SecurityBundle\Entity\User as EntityUser;
use OHMedia\SecurityBundle\Security\Voter\SingleAttributeVoter;

class <?= $singular['pascal_case'] ?><?= $crud ?>Voter extends SingleAttributeVoter
{
    protected function getSubjectType(): string
    {
        return <?= $singular['pascal_case'] ?>::class;
    }

    protected function voteOnSubject($<?= $singular['camel_case'] ?>, EntityUser $loggedIn): bool
    {
        return true;
    }
}