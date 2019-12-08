<?php

declare(strict_types=1);

namespace Scheb\TwoFactorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Decorates all remember-me services instances so that the remember-me cookie doesn't leak when two-factor
 * authentication is required.
 */
class RememberMeServicesDecoratorCompilerPass implements CompilerPassInterface
{
    public const REMEMBER_ME_SERVICES_TAG = 'scheb_two_factor.security.rememberme_services';

    public function process(ContainerBuilder $container)
    {
        // Find all remember-me services definitions
        foreach ($container->findTaggedServiceIds(self::REMEMBER_ME_SERVICES_TAG) as $definitionId => $attributes) {
            $this->decorateRememberMeServices($container, $definitionId);
            $this->clearTag($container, $definitionId);
        }
    }

    private function decorateRememberMeServices(ContainerBuilder $container, string $rememberMeServicesId): void
    {
        $decoratedServiceId = $rememberMeServicesId.'.two_factor_decorator';
        $container
            ->setDefinition($decoratedServiceId, new ChildDefinition(self::REMEMBER_ME_SERVICES_TAG.'_decorator'))
            ->setDecoratedService($rememberMeServicesId)
            ->replaceArgument(0, new Reference($decoratedServiceId.'.inner'));
    }

    private function clearTag(ContainerBuilder $container, string $rememberMeServicesId): void
    {
        $container
            ->findDefinition($rememberMeServicesId)
            ->clearTag(self::REMEMBER_ME_SERVICES_TAG);

    }
}
