<?php

/*
 * This file is part of the Search PHP Bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 * @author PuntMig Technologies
 */

declare(strict_types=1);

namespace Apisearch\DependencyInjection;

use Apisearch\Http\Retry;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * File header placeholder.
 */
class ApisearchConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('apisearch');
        $this->setupTree($rootNode);

        return $treeBuilder;
    }

    /**
     * Configure the root node.
     *
     * @param ArrayNodeDefinition $rootNode Root node
     */
    protected function setupTree(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('repositories')
                    ->prototype('array')
                        ->children()
                            ->enumNode('adapter')
                                ->values(['http', 'http_test', 'service', 'in_memory'])
                                ->defaultValue('http')
                            ->end()
                            ->arrayNode('http')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->arrayNode('retry_map')
                                        ->prototype('array')
                                            ->children()
                                                ->scalarNode('url')
                                                    ->defaultValue('*')
                                                ->end()
                                                ->scalarNode('method')
                                                    ->defaultValue('*')
                                                ->end()
                                                ->integerNode('retries')
                                                    ->defaultValue(0)
                                                ->end()
                                                ->integerNode('microseconds_between_retries')
                                                    ->defaultValue(Retry::DEFAULT_MICROSECONDS_BETWEEN_RETRIES)
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->scalarNode('app_id')
                                ->defaultNull()
                            ->end()
                            ->scalarNode('token')
                                ->defaultNull()
                            ->end()
                            ->scalarNode('endpoint')
                                ->defaultNull()
                            ->end()
                            ->scalarNode('version')
                                ->defaultValue('v1')
                            ->end()
                            ->arrayNode('indexes')
                                ->scalarPrototype()
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
                            ->booleanNode('test')
                                ->defaultFalse()
                            ->end()
                            ->arrayNode('search')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('repository_service')
                                        ->defaultValue('apisearch.repository_mock')
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('app')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('repository_service')
                                        ->defaultValue('apisearch.app_repository_mock')
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('event')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('repository_service')
                                        ->defaultValue('apisearch.events_repository_mock')
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('log')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('repository_service')
                                        ->defaultValue('apisearch.logs_repository_mock')
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('user')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('repository_service')
                                        ->defaultValue('apisearch.user_repository_mock')
                                    ->end()
                                ->end()
                            ->end();
    }
}
