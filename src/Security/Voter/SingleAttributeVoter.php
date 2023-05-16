<?php

namespace OHMedia\SecurityBundle\Security\Voter;

use OHMedia\SecurityBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class SingleAttributeVoter extends Voter
{
    abstract protected function getSubjectType(): string;
    abstract protected function voteOnSubject($subject, User $loggedIn): bool;

    public function supportsAttribute(string $attribute): bool
    {
        return static::class === $attribute;
    }

    public function supportsType(string $subjectType): bool
    {
        return $this->getSubjectType() === $subjectType;
    }

    protected function supports(string $attribute, $subject): bool
    {
        if (!$this->supportsAttribute($attribute)) {
            return false;
        }

        $subjectString = \is_object($subject) ? \get_class($subject) : get_debug_type($subject);

        return $this->supportsType($subjectString);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $loggedIn = $token->getUser();

        if (!$loggedIn instanceof User) {
            return false;
        }

        if (!$this->isAttributeAccessibleByUser($attribute, $loggedIn)) {
            return false;
        }

        return $this->voteOnSubject($subject, $loggedIn);
    }

    private function isAttributeAccessibleByUser(string $attribute, User $loggedIn): bool
    {
        if ($loggedIn->isDeveloper()) {
            return true;
        }

        foreach ($loggedIn->getUserRoles() as $role) {
            if (in_array($attribute, $role->getAttributes())) {
                return true;
            }
        }

        return false;
    }
}
