<?php

namespace Scheb\TwoFactorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

class ProviderCompilerPass implements CompilerPassInterface
{
    /**
     * Collect registered two-factor providers and register them.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('scheb_two_factor.provider_collection')) {
            return;
        }

        $definition = $container->getDefinition('scheb_two_factor.provider_collection');
        $taggedServices = $container->findTaggedServiceIds('scheb_two_factor.provider');
        $references = array();
        foreach ($taggedServices as $id => $attributes) {
            if (!isset($attributes[0]['alias'])) {
                throw new InvalidArgumentException('Tag "scheb_two_factor.provider" requires attribute "alias" to be set.');
            }
            $name = $attributes[0]['alias'];
            $definition->addMethodCall(
                'addProvider',
                array($name, new Reference($id))
            );
        }
    }
}
