<?php

namespace OHMedia\SecurityBundle;

use OHMedia\SecurityBundle\DependencyInjection\Compiler\EntityChoicePass;
use OHMedia\SecurityBundle\Service\EntityChoiceInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class OHMediaSecurityBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new EntityChoicePass());
    }

    public function loadExtension(
        array $config,
        ContainerConfigurator $containerConfigurator,
        ContainerBuilder $containerBuilder
    ): void {
        $containerConfigurator->import('../config/services.yaml');

        $containerBuilder->registerForAutoconfiguration(EntityChoiceInterface::class)
            ->addTag('oh_media_security.entity_choice')
        ;
    }
}
