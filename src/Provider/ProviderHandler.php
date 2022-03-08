<?php

namespace OHMedia\SecurityBundle\Provider;

class ProviderHandler
{
    private $providers;
    
    public function __construct(iterable $providers)
    {
        $this->providers = $providers;
    }
    
    public function getEntityActions()
    {
        $entity_actions = [];
        
        foreach ($this->providers as $provider) {
            $entity_actions[] = [
                'name' => $provider->getEntityName(),
                'actions' => $provider->getEntityActions()
            ];
        }
        
        usort($entity_actions, function($a, $b) {
            return $a['name'] <=> $b['name'];
        });
        
        return $entity_actions;
    }
}
