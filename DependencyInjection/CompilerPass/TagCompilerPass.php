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

namespace Apisearch\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class TagCompilerPass.
 */
abstract class TagCompilerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has($this->getCollectorServiceName())) {
            return;
        }

        $definition = $container->findDefinition(
            $this->getCollectorServiceName()
        );

        $taggedServices = $container->findTaggedServiceIds($this->getTagName());

        /*
         * Per each service, add a new method call reference
         */
        foreach ($taggedServices as $service => $tags) {
            $definition->addMethodCall(
                $this->getCollectorMethodName(),
                [$service]
            );
        }
    }

    /**
     * Get collector service name.
     *
     * @return string Collector service name
     */
    abstract public function getCollectorServiceName(): string;

    /**
     * Get collector method name.
     *
     * @return string Collector method name
     */
    abstract public function getCollectorMethodName(): string;

    /**
     * Get tag name.
     *
     * @return string Tag name
     */
    abstract public function getTagName(): string;
}
