<?php

namespace OHMedia\SecurityBundle\Security\Voter;

use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Security\Voter\EntityVoter;

class UserVoter extends EntityVoter
{
    const ATTRIBUTE_PREFIX = 'user_';
    const INDEX = self::ATTRIBUTE_PREFIX . 'index';
    const CREATE = self::ATTRIBUTE_PREFIX . 'create';
    const EDIT = self::ATTRIBUTE_PREFIX . 'edit';
    const DELETE = self::ATTRIBUTE_PREFIX . 'delete';

    protected function getEntityClass(): string
    {
        return User::class;
    }

    protected function canIndex(User $user, User $loggedIn): bool
    {
        return true;
    }

    protected function canCreate(User $user, User $loggedIn): bool
    {
        return true;
    }

    protected function canEdit(User $user, User $loggedIn): bool
    {
        if ($user->isDeveloper()) {
            // can only be edited by other developer users
            return $loggedIn->isDeveloper();
        }

        return true;
    }

    protected function canDelete(User $user, User $loggedIn): bool
    {
        if ($user->isDeveloper()) {
            // developer user cannot be deleted
            return false;
        }

        // user cannot delete themselves
        return $user !== $loggedIn;
    }
}
