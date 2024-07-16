<?php

namespace OHMedia\SecurityBundle\Security\Voter;

use OHMedia\SecurityBundle\Entity\User;

class UserVoter extends AbstractEntityVoter
{
    public const INDEX = 'index';
    public const CREATE = 'create';
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    protected function getAttributes(): array
    {
        return [
            self::INDEX,
            self::CREATE,
            self::EDIT,
            self::DELETE,
        ];
    }

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
        if ($user->isTypeDeveloper()) {
            // can only be edited by other developer users
            return $loggedIn->isTypeDeveloper();
        }

        return true;
    }

    protected function canDelete(User $user, User $loggedIn): bool
    {
        if ($user->isTypeDeveloper()) {
            // developer user cannot be deleted
            return false;
        }

        // user cannot delete themselves
        return $user !== $loggedIn;
    }
}
