<?php

namespace OHMedia\SecurityBundle\Service;

use OHMedia\BackendBundle\Service\AbstractNavItemProvider;
use OHMedia\BootstrapBundle\Component\Nav\NavItemInterface;
use OHMedia\BootstrapBundle\Component\Nav\NavLink;
use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Security\Voter\UserVoter;

class UserNavItemProvider extends AbstractNavItemProvider
{
    public function getNavItem(): ?NavItemInterface
    {
        if ($this->isGranted(UserVoter::INDEX, new User())) {
            return (new NavLink('Users', 'user_index'))
                ->setIcon('lock-fill');
        }

        return null;
    }
}
