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
        $entityActions = [];

        foreach ($this->providers as $provider) {
            $entityActions[] = [
                'name' => $provider->getEntityName(),
                'actions' => $provider->getEntityActions()
            ];
        }

        usort($entityActions, function($a, $b) {
            return $a['name'] <=> $b['name'];
        });

        return $entityActions;
    }
}
