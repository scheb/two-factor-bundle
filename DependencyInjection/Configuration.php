<?php
namespace Scheb\TwoFactorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('scheb_two_factor');

        $rootNode
            ->children()
                ->scalarNode("persister")->defaultNull()->end()
                ->scalarNode("model_manager_name")->defaultNull()->end()
                ->arrayNode("trusted_computer")
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode("enabled")->defaultFalse()->end()
                        ->scalarNode("cookie_name")->defaultValue("trusted_computer")->end()
                        ->scalarNode("cookie_lifetime")->defaultValue(60*24*3600)->end()
                    ->end()
                ->end()
                ->scalarNode("exclude_pattern")->defaultNull()->end()
                ->arrayNode("parameter_names")
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode("auth_code")->defaultValue("_auth_code")->end()
                        ->scalarNode("trusted")->defaultValue("_trusted")->end()
                    ->end()
                ->end()
                ->arrayNode("email")
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode("enabled")->defaultFalse()->end()
                        ->scalarNode("mailer")->defaultNull()->end()
                        ->scalarNode("sender_email")->defaultValue("no-reply@example.com")->end()
                        ->scalarNode("sender_name")->defaultNull()->end()
                        ->scalarNode("template")->defaultValue("SchebTwoFactorBundle:Authentication:form.html.twig")->end()
                        ->scalarNode("digits")->defaultValue(4)->end()
                    ->end()
                ->end()
                ->arrayNode("google")
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode("enabled")->defaultFalse()->end()
                        ->scalarNode("issuer")->defaultNull()->end()
                        ->scalarNode("server_name")->defaultNull()->end()
                        ->scalarNode("template")->defaultValue("SchebTwoFactorBundle:Authentication:form.html.twig")->end()
                        ->scalarNode("email_template")->defaultValue("SchebTwoFactorBundle:Email:qr.html.twig")->end()
                    ->end()
                ->end()
                ->arrayNode("security_tokens")
                    ->defaultValue(array("Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken"))
                    ->prototype("scalar")->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
