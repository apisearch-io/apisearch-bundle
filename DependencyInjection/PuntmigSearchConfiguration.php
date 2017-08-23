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

namespace Puntmig\Search\DependencyInjection;

use Mmoreram\BaseBundle\DependencyInjection\BaseConfiguration;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * File header placeholder.
 */
class PuntmigSearchConfiguration extends BaseConfiguration
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
                ->arrayNode('repositories')
                    ->prototype('array')
                        ->children()
                            ->booleanNode('http')
                                ->defaultTrue()
                            ->end()
                            ->scalarNode('secret')
                                ->defaultNull()
                            ->end()
                            ->scalarNode('endpoint')
                                ->defaultNull()
                            ->end()
                            ->booleanNode('test')
                                ->defaultFalse()
                            ->end()
                            ->arrayNode('search')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('repository_service')
                                        ->defaultNull()
                                    ->end()
                                    ->booleanNode('in_memory')
                                        ->defaultFalse()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('event')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('repository_service')
                                        ->defaultNull()
                                    ->end()
                                    ->booleanNode('in_memory')
                                        ->defaultFalse()
                                    ->end()
                                ->end()
                            ->end();
    }
}
