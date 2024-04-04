<?php

namespace OHMedia\SecurityBundle;

use OHMedia\SecurityBundle\DependencyInjection\Compiler\EntityChoicePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OHMediaSecurityBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new EntityChoicePass());
    }
}
