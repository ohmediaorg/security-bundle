<?php

declare(strict_types=1);

namespace OHMedia\SecurityBundle\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use OHMedia\SecurityBundle\Entity\EntityUser;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class AbstractUserMigration extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

    abstract protected function getUser(UserPasswordHasherInterface $hasher): EntityUser;

    final public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getDescription() : string
    {
        return '';
    }

    final public function up(Schema $schema) : void
    {
    }

    final public function postUp(Schema $schema) : void
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $hasher = $this->container->get('security.password_hasher');

        $user = $this->getUser($hasher);

        $em->persist($user);
        $em->flush();
    }

    final public function down(Schema $schema) : void
    {
    }
}
