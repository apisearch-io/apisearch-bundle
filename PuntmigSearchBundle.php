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

namespace Puntmig\Search;

use Mmoreram\BaseBundle\BaseBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\KernelInterface;

use Puntmig\Search\DependencyInjection\CompilerPass\ReadTransformerCompilerPass;
use Puntmig\Search\DependencyInjection\CompilerPass\RepositoryCompilerPass;
use Puntmig\Search\DependencyInjection\CompilerPass\WriteTransformerCompilerPass;
use Puntmig\Search\DependencyInjection\PuntmigSearchExtension;

/**
 * Class PuntmigSearchBundle.
 */
class PuntmigSearchBundle extends BaseBundle
{
    /**
     * Returns the bundle's container extension.
     *
     * @return ExtensionInterface|null The container extension
     */
    public function getContainerExtension()
    {
        return new PuntmigSearchExtension();
    }

    /**
     * Return all bundle dependencies.
     *
     * Values can be a simple bundle namespace or its instance
     *
     * @return array
     */
    public static function getBundleDependencies(KernelInterface $kernel) : array
    {
        return [
            FrameworkBundle::class,
        ];
    }

    /**
     * Return a CompilerPass instance array.
     *
     * @return CompilerPassInterface[]
     */
    public function getCompilerPasses() : array
    {
        return [
            new RepositoryCompilerPass(),
            new ReadTransformerCompilerPass(),
            new WriteTransformerCompilerPass(),
        ];
    }
}
