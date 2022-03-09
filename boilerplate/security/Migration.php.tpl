<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\__PASCALCASE__;
use OHMedia\SecurityBundle\Doctrine\Migrations\AbstractUserMigration;
use OHMedia\SecurityBundle\Entity\User as EntityUser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class __MIGRATIONCLASS__ extends AbstractUserMigration
{
    protected function getUser(UserPasswordHasherInterface $hasher): EntityUser
    {
        $__CAMELCASE__ = new __PASCALCASE__();

        // Best practice to set an easy password
        // then immediately log in and change it.
        // Don't commit passwords in git!
        $hashed = $hasher->hashPassword($__CAMELCASE__, '123456');

        $__CAMELCASE__
            // set the email (ie. username) for logging in
            ->setEmail('justin@ohmedia.ca')
            ->setPassword($hashed)

            // ...populate other fields as needed
        ;

        return $__CAMELCASE__;
    }
}
