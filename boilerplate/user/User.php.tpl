<?php

namespace App\Entity;

use App\Repository\__PASCALCASE__Repository;
use Doctrine\ORM\Mapping as ORM;
use OHMedia\SecurityBundle\Entity\User as EntityUser;

#[ORM\Entity(repositoryClass: __PASCALCASE__Repository::class)]
class __PASCALCASE__ extends EntityUser
{
}
