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
 */

declare(strict_types=1);

namespace Apisearch;

use Apisearch\DependencyInjection\ApisearchExtension;
use Apisearch\DependencyInjection\CompilerPass\ExporterCompilerPass;
use Apisearch\DependencyInjection\CompilerPass\ReadTransformerCompilerPass;
use Apisearch\DependencyInjection\CompilerPass\RepositoryCompilerPass;
use Apisearch\DependencyInjection\CompilerPass\WriteTransformerCompilerPass;
use Mmoreram\BaseBundle\BaseBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class ApisearchBundle.
 */
class ApisearchBundle extends BaseBundle
{
    /**
     * Return a CompilerPass instance array.
     *
     * @return CompilerPassInterface[]
     */
    public function getCompilerPasses(): array
    {
        return [
            new RepositoryCompilerPass(),
            new ReadTransformerCompilerPass(),
            new WriteTransformerCompilerPass(),
            new ExporterCompilerPass(),
        ];
    }

    /**
     * Returns the bundle's container extension.
     *
     * @return ExtensionInterface|null The container extension
     */
    public function getContainerExtension()
    {
        return new ApisearchExtension();
    }

    /**
     * Return all bundle dependencies.
     *
     * Values can be a simple bundle namespace or its instance
     *
     * @return array
     */
    public static function getBundleDependencies(KernelInterface $kernel): array
    {
        return [
            BaseBundle::class,
            FrameworkBundle::class,
        ];
    }
}
