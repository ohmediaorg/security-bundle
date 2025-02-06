<?php

namespace OHMedia\SecurityBundle\EventListener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Service\UserLifecycle;

class UserPreUpdate
{
    public function __construct(private UserLifecycle $userLifecycle)
    {
    }

    public function preUpdate(User $user, PreUpdateEventArgs $event)
    {
        $this->userLifecycle->preUpdate($user, $event);
    }
}
