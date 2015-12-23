<?php

namespace Scheb\TwoFactorBundle;

use Scheb\TwoFactorBundle\DependencyInjection\Compiler\ProviderCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SchebTwoFactorBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        // Add compiler pass to register two-factor providers
        $container->addCompilerPass(new ProviderCompilerPass());
    }
}
