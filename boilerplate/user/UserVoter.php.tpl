<?php

namespace App\Security\Voter;

use App\Entity\__PASCALCASE__;
use App\Provider\__PASCALCASE__Provider;
use OHMedia\SecurityBundle\Security\Voter\EntityVoter;

class __PASCALCASE__Voter extends EntityVoter
{
    public function __construct(__PASCALCASE__Provider $provider)
    {
        $this->setProvider($provider);
    }

    protected function canCreate(__PASCALCASE__ $__CAMELCASE__, User $loggedIn)
    {
        return true;
    }

    protected function canRead(__PASCALCASE__ $__CAMELCASE__, User $loggedIn)
    {
        return true;
    }

    protected function canUpdate(__PASCALCASE__ $__CAMELCASE__, User $loggedIn)
    {
        return true;
    }

    protected function canDelete(__PASCALCASE__ $__CAMELCASE__, User $loggedIn)
    {
        return true;
    }
}
