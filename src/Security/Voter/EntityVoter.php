<?php

namespace OHMedia\SecurityBundle\Security\Voter;

use OHMedia\SecurityBundle\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

use function Symfony\Component\String\u;

abstract class EntityVoter extends Voter
{
    protected function getEntityClass(): string;
    protected function getAttributes(): array;

    public function supportsAttribute(string $attribute): bool
    {
        return in_array($attribute, $this->getAttributes());
    }

    public function supportsType(string $subjectType): bool
    {
        return is_a($subjectType, $this->getEntityClass(), true);
    }

    protected function supports(string $attribute, $subject): bool
    {
        if (!$this->supportsAttribute($attribute)) {
            return false;
        }

        $class = $this->getEntityClass();

        return $subject instanceof $class || $subject === $class;
    }

    protected function voteOnAttribute($action, $entity, TokenInterface $token): bool
    {
        $loggedIn = $token->getUser();

        if (!$loggedIn instanceof User) {
            return false;
        }

        $method = 'can' . u($action)->camel()->title();

        if (!method_exists($this, $method)) {
            throw new LogicException(sprintf(
                'Your voter "\%s" should implement %s()',
                static::class,
                $method
            ));
        }

        return call_user_func_array(
            [$this, $method],
            [$entity, $loggedIn]
        );
    }
}