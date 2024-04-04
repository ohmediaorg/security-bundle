<?php

namespace OHMedia\SecurityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class EntityChoicePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // always first check if the primary service is defined
        if (!$container->has('oh_media_security.entity_choice_manager')) {
            return;
        }

        $definition = $container->findDefinition('oh_media_security.entity_choice_manager');

        $tagged = $container->findTaggedServiceIds('oh_media_security.entity_choice');

        foreach ($tagged as $id => $tags) {
            $definition->addMethodCall('addEntityChoice', [new Reference($id)]);
        }
    }
}
