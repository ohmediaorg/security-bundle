<?php

namespace OHMedia\SecurityBundle\EventListener;

use Doctrine\ORM\Event\PostUpdateEventArgs;
use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Service\UserLifecycle;

class UserPostUpdate
{
    public function __construct(private UserLifecycle $userLifecycle)
    {
    }

    public function postUpdate(User $user, PostUpdateEventArgs $event)
    {
        $this->userLifecycle->postUpdate($user, $event);
    }
}
