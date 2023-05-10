<?php

namespace OHMedia\SecurityBundle\Security\Voter;

use OHMedia\SecurityBundle\Entity\Entity;
use OHMedia\SecurityBundle\Entity\Traits\Lockable as LockableTrait;
use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Provider\AbstractEntityProvider;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

use function Symfony\Component\String\u;

abstract class EntityVoter extends Voter
{
    protected $provider;

    protected function setProvider(AbstractEntityProvider $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    public function supportsAttribute(string $attribute): bool
    {
        $actions = $this->provider->getActions();
        $actions[] = 'unlock';

        return in_array($attribute, $actions);
    }

    public function supportsType(string $subjectType): bool
    {
        return $subjectType === $this->provider->getEntityClass();
    }

    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof Entity
            && $this->supportsAttribute($attribute)
            && $this->subjectSupported($subject);
    }

    protected function subjectSupported($subject): bool
    {
        $class = $this->provider->getEntityClass();

        return $subject instanceof $class;
    }

    protected function isActionAccessibleByUser($action, User $loggedIn): bool
    {
        if ($loggedIn->isDeveloper()) {
            return true;
        }

        $entityAction = $this->provider->getEntityAction($action);

        foreach ($loggedIn->getUserRoles() as $role) {
            if (in_array($entityAction, $role->getActions())) {
                return true;
            }
        }

        return false;
    }

    protected function voteOnAttribute($action, $entity, TokenInterface $token): bool
    {
        $loggedIn = $token->getUser();

        if (!$loggedIn instanceof User) {
            return false;
        }

        //if (!$this->isActionAccessibleByUser($action, $loggedIn)) {
        //    return false;
        //}

        $method = 'can' . u($action)->camel()->title();

        if (method_exists($this, $method)) {
            return call_user_func_array(
                [$this, $method],
                [$entity, $loggedIn]
            );
        }

        throw new LogicException(sprintf('Your voter "\%s" should implement %s()', static::class, $method));
    }

    final protected function canUnlock($entity, User $loggedIn): bool
    {
        if (!in_array(LockableTrait::class, class_uses($entity))) {
            return false;
        }

        if ($loggedIn->isDeveloper()) {
            return true;
        }

        if ($entity->isUserLocked($loggedIn) && !$entity->isUnlockable()) {
            return false;
        }

        return $this->canUnlockEntity($entity, $loggedIn);
    }

    /**
     * Override this for additional custom unlock checking
     */
    protected function canUnlockEntity($entity, User $loggedIn): bool
    {
        return true;
    }
}

// TODO: look into passing fully-qualified class names as voter attributes

/*interface VoterAttributeInterface
{
    public function getName(): string;
    public function getDescription(): string;
    public function vote(Entity $entity, EntityUser $user): bool;
}*/
