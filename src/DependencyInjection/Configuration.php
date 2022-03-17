<?php

namespace OHMedia\SecurityBundle\DependencyInjection;

use DateTimeZone;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('oh_media_security');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('timezone')
                    ->defaultValue('America/Regina')
                    ->validate()
                    ->ifNotInArray(DateTimeZone::listIdentifiers())
                        ->thenInvalid('Invalid timezone "%s"')
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
