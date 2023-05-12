<?php

namespace App\Security\Voter;

use App\Entity\__PASCALCASE__;
use App\Entity\User;
use App\Provider\__PASCALCASE__Provider;
use OHMedia\SecurityBundle\Security\Voter\EntityVoter;

class __PASCALCASE__Voter extends EntityVoter
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
        return __PASCALCASE__::class;
    }

    protected function canCreate(__PASCALCASE__ $__CAMELCASE__, User $loggedIn): bool
    {
        return true;
    }

    protected function canView(__PASCALCASE__ $__CAMELCASE__, User $loggedIn): bool
    {
        return true;
    }

    protected function canEdit(__PASCALCASE__ $__CAMELCASE__, User $loggedIn): bool
    {
        return true;
    }

    protected function canDelete(__PASCALCASE__ $__CAMELCASE__, User $loggedIn): bool
    {
        return true;
    }
}
