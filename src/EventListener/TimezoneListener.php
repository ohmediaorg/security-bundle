<?php

namespace OHMedia\SecurityBundle\EventListener;

use OHMedia\SecurityBundle\Entity\User;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TimezoneListener
{
    private $tokenStorage;
    private $defaultTimezone;

    public function __construct(TokenStorageInterface $tokenStorage, $defaultTimezone)
    {
        $this->tokenStorage = $tokenStorage;
        $this->defaultTimezone = $defaultTimezone;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $token = $this->tokenStorage->getToken();

        $user = $token ? $token->getUser() : null;
        $timezone = $user instanceof User
            ? $user->getTimezone()
            : null;

        if (empty($timezone)) {
            $timezone = $this->defaultTimezone;
        }

        date_default_timezone_set($timezone);
    }
}
