<?php

namespace OHMedia\SecurityBundle\EventListener;

use Doctrine\ORM\Event\PrePersistEventArgs;
use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Service\UserLifecycle;

class UserPrePersist
{
    public function __construct(private UserLifecycle $userLifecycle)
    {
    }

    public function prePersist(User $user, PrePersistEventArgs $event)
    {
        $this->userLifecycle->prePersist($user, $event);
    }
}
