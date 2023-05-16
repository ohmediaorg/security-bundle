<?= "<?php\n" ?>

namespace App\Security\Voter;

use App\Entity\<?= $singular['pascal_case'] ?>;
use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Security\Voter\SingleAttributeVoter;

class <?= $singular['pascal_case'] ?>IndexVoter extends SingleAttributeVoter
{
    protected function getSubjectType(): string
    {
        return <?= $singular['pascal_case'] ?>::class;
    }

    protected function voteOnSubject($<?= $singular['camel_case'] ?>, User $user): bool
    {
        return true;
    }
}