<?php

namespace OHMedia\SecurityBundle\Security\Voter;

use OHMedia\SecurityBundle\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

use function Symfony\Component\String\u;

abstract class EntityVoter extends Voter
{
    abstract public function getAttributes(): array;
    abstract public function getEntityClass(): string;

    public function supportsAttribute(string $attribute): bool
    {
        return in_array($attribute, $this->getAttributes());
    }

    public function supportsType(string $subjectType): bool
    {
        return 'null' === $subjectType || $this->getEntityClass() === $subjectType;
    }

    protected function supports(string $attribute, $subject): bool
    {
        $class = $this->getEntityClass();

        if (!$subject && $attribute === static::INDEX) {
            return true;
        }

        return $subject instanceof $class && $this->supportsAttribute($attribute);
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

        $method = 'can' . u($attribute)->camel()->title();

        if (!method_exists($this, $method)) {
            throw new LogicException(sprintf('Your voter "\%s" should implement %s()', static::class, $method));
        }

        if ($attribute === static::INDEX) {
            return call_user_func_array(
                [$this, $method],
                [$loggedIn]
            );
        }

        return call_user_func_array(
            [$this, $method],
            [$subject, $loggedIn]
        );
    }

    protected function isAttributeAccessibleByUser(string $attribute, User $loggedIn): bool
    {
        if ($loggedIn->isDeveloper()) {
            return true;
        }

        $entityAttribute = $this->getEntityAttribute($attribute);

        foreach ($loggedIn->getUserRoles() as $role) {
            if (in_array($entityAttribute, $role->getAttributes())) {
                return true;
            }
        }

        return false;
    }

    private function getEntityAttributes(): array
    {
        $entityAttributes = [];

        foreach ($this->getAttributes() as $attribute) {
            $entityAttributes[] = $this->getEntityAttribute($attribute);
        }

        return $entityAttributes;
    }

    final public function getEntityAttribute(string $attribute): string
    {
        $className = $this->getClassName();

        return sprintf('%s_%s', u($className)->snake(), $attribute);
    }

    final public function getClassName(): string
    {
        $entityName = explode('\\', $this->getEntityClass());

        $entityName = array_pop($entityName);

        return $entityName;
    }

    final public function getEntityName(): string
    {
        $className = $this->getClassName();

        return u($className)->title(true);
    }
}
