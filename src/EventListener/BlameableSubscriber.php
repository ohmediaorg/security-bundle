<?php

namespace OHMedia\SecurityBundle\EventListener;

use DateTime;
use OHMedia\SecurityBundle\Entity\Traits\BlameableTrait;
use OHMedia\SecurityBundle\Entity\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BlameableSubscriber implements EventSubscriber
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $this->addBlameables(true, $args);
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->addBlameables(false, $args);
    }

    private function addBlameables($creating, LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        $class = get_class($entity);

        if (!in_array(BlameableTrait::class, $this->getTraits($class))) {
            return;
        }

        $token = $this->tokenStorage->getToken();

        $user = $token ? $token->getUser() : null;
        $blame = $user instanceof User
            ? $user->getEmail()
            : null;

        $now = new DateTime();

        if ($creating) {
            $entity
                ->setCreatedAt($now)
                ->setCreatedBy($blame)
            ;
        }

        $entity
            ->setUpdatedAt($now)
            ->setUpdatedBy($blame)
        ;
    }

    private function getTraits($class)
    {
        $traits = [];

        do {
            $traits = array_merge(class_uses($class), $traits);
        } while($class = get_parent_class($class));

        foreach ($traits as $trait => $same) {
            $traits = array_merge(class_uses($trait), $traits);
        }

        return array_unique($traits);
    }
}
