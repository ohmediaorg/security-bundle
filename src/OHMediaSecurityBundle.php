<?php

namespace OHMedia\SecurityBundle;

use OHMedia\SecurityBundle\DependencyInjection\Compiler\EntityChoicePass;
use OHMedia\SecurityBundle\Service\EntityChoiceInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Validator\Constraints\PasswordStrength;

class OHMediaSecurityBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new EntityChoicePass());
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->integerNode('password_strength')
                    ->min(PasswordStrength::STRENGTH_VERY_WEAK)
                    ->max(PasswordStrength::STRENGTH_VERY_STRONG)
                    ->defaultValue(PasswordStrength::STRENGTH_VERY_WEAK)
                ->end()
            ->end()
        ;
    }

    public function loadExtension(
        array $config,
        ContainerConfigurator $containerConfigurator,
        ContainerBuilder $containerBuilder
    ): void {
        $containerConfigurator->import('../config/services.yaml');

        $containerConfigurator->parameters()
            ->set('oh_media_security.password_strength', $config['password_strength'])
        ;

        $containerBuilder->registerForAutoconfiguration(EntityChoiceInterface::class)
            ->addTag('oh_media_security.entity_choice')
        ;
    }
}
