<?php

/*
 * This file is part of the Apisearch Bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

declare(strict_types=1);

namespace Apisearch\DependencyInjection;

use Apisearch\App\MockAppRepository;
use Apisearch\Http\Retry;
use Apisearch\Repository\MockRepository;
use Apisearch\User\MockUserRepository;
use Mmoreram\BaseBundle\DependencyInjection\BaseConfiguration;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * File header placeholder.
 */
class ApisearchConfiguration extends BaseConfiguration
{
    /**
     * Configure the root node.
     *
     * @param ArrayNodeDefinition $rootNode Root node
     */
    protected function setupTree(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->booleanNode('load_commands')
                    ->defaultTrue()
                ->end()
                ->arrayNode('repositories')
                    ->prototype('array')
                        ->children()
                            ->enumNode('adapter')
                                ->values(['http', 'http_test', 'service', 'in_memory', 'disk'])
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
                            ->scalarNode('disk_file')
                                ->defaultValue('/tmp/apisearch.repository')
                            ->end()
                            ->arrayNode('indices')
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
                                        ->defaultValue(MockRepository::class)
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('app')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('repository_service')
                                        ->defaultValue(MockAppRepository::class)
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('user')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('repository_service')
                                        ->defaultValue(MockUserRepository::class)
                                    ->end()
                                ->end()
                            ->end();
    }
}
