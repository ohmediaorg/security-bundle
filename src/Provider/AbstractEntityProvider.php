<?php

namespace JstnThms\SecurityBundle\Provider;

use Doctrine\ORM\EntityManagerInterface;
use JstnThms\SecurityBundle\Entity\Entity;

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
        $entity_actions = [];
        
        foreach ($this->getActions() as $action) {
            $entity_actions[] = $this->getEntityAction($action);
        }
        
        return $entity_actions;
    }
    
    final public function getEntityAction(string $action): string
    {
        $class_name = $this->getClassName();
        
        return sprintf('%s_%s', u($class_name)->snake(), $action);
    }
    
    final public function getClassName(): string
    {
        $entity_name = explode('\\', $this->getEntityClass());
        
        $entity_name = array_pop($entity_name);
        
        return $entity_name;
    }
    
    final public function getEntityName(): string
    {
        $class_name = $this->getClassName();
        
        return u($class_name)->title(true);
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
