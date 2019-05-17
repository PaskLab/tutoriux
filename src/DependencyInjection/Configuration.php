<?php

namespace App\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

/**
 * Class Configuration
 * @package App\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('app');

        $this->addSettings($treeBuilder->getRootNode());
        return $treeBuilder;
    }

    /**
     * @param NodeDefinition $rootNode
     */
    private function addSettings(NodeDefinition $rootNode)
    {
        $rootNode
            ->children()

                ->arrayNode('metadata')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('title')->defaultNull()->end()
                        ->scalarNode('description')->defaultNull()->end()
                        ->scalarNode('keywords')->defaultNull()->end()
                    ->end()
                ->end()

                ->arrayNode('notification')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default_expiration')
                            ->defaultValue('P1M')
                            ->info('\\DateInterval string')
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('log')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default_expiration')
                            ->defaultValue('P3M')
                            ->info('\\DateInterval string')
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('emails')
                    ->addDefaultsIfNotSet()
                    ->children()

                        ->scalarNode('system_email')
                            ->defaultValue('system@tutoriux.com')
                            ->info('Email address used to send system email. Ex: LostPassword')
                        ->end()

                        ->scalarNode('feedback')
                            ->defaultValue('feedback@tutoriux.com')
                            ->info('Email address to which feedback emails are sent')
                        ->end()

                    ->end()
                ->end()

            ->end();
    }
}