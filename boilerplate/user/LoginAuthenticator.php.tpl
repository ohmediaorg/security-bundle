<?php

namespace App\Security;

use App\Entity\__PASCALCASE__;
use OHMedia\SecurityBundle\Security\AbstractUserAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class LoginAuthenticator extends AbstractUserAuthenticator
{
    protected function getLoginRoute()
    {
        return 'app_login';
    }

    protected function getLoginSuccessRoute(TokenInterface $token)
    {
        return 'app_home';
    }

    protected function getUserClass()
    {
        return __PASCALCASE__::class;
    }
}
