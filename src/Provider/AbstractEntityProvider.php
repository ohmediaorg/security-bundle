<?php

namespace OHMedia\SecurityBundle\Provider;

use Doctrine\ORM\EntityManagerInterface;
use OHMedia\SecurityBundle\Entity\Entity;

use function Symfony\Component\String\u;

abstract class AbstractEntityProvider
{
    protected $em;

    abstract public function getEntityClass(): string;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public static function getHumanReadable(): string
    {
        return 'entity';
    }

    final public function getActions(): array
    {
        $actions = array_merge(
            $this->getCustomActions(),
            ['create', 'view', 'edit', 'delete']
        );

        return array_unique($actions);
    }

    protected function getCustomActions(): array
    {
        return [];
    }

    final public function getEntityActions(): array
    {
        $entityActions = [];

        foreach ($this->getActions() as $action) {
            $entityActions[] = $this->getEntityAction($action);
        }

        return $entityActions;
    }

    final public function getEntityAction(string $action): string
    {
        $className = $this->getClassName();

        return sprintf('%s_%s', u($className)->snake(), $action);
    }

    final public function getClassName(): string
    {
        $entityName = explode('\\', $this->getEntityClass());

        $entityName = array_pop($entityName);

        return $entityName;
    }

    final public function getEntityName(): string
    {
        $className = $this->getClassName();

        return u($className)->title(true);
    }

    public function getEntityRepository()
    {
        return $this->em->getRepository($this->getEntityClass());
    }

    public function create(): Entity
    {
        $class = $this->getEntityClass();

        return new $class();
    }

    public function get($id): ?Entity
    {
        return $this->getEntityRepository()->find($id);
    }

    public function save(Entity $entity)
    {
        if (!$entity->getId()) {
            $this->em->persist($entity);
        }

        $this->em->flush();
    }

    public function delete(Entity $entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }
}
