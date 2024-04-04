<?php

namespace OHMedia\SecurityBundle\Service;

use OHMedia\SecurityBundle\Entity\User;

class UserEntityChoice implements EntityChoiceInterface
{
    public function getLabel(): string
    {
        return 'Users';
    }

    public function getEntities(): array
    {
        return [User::class];
    }
}
