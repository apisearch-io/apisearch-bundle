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

namespace Apisearch;

use Apisearch\DependencyInjection\ApisearchExtension;
use Apisearch\DependencyInjection\CompilerPass\ExporterCompilerPass;
use Apisearch\DependencyInjection\CompilerPass\ReadTransformerCompilerPass;
use Apisearch\DependencyInjection\CompilerPass\RepositoryCompilerPass;
use Apisearch\DependencyInjection\CompilerPass\WriteTransformerCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class ApisearchBundle.
 */
class ApisearchBundle extends Bundle
{
    /**
     * Builds bundle.
     *
     * @param ContainerBuilder $container Container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RepositoryCompilerPass());
        $container->addCompilerPass(new ReadTransformerCompilerPass());
        $container->addCompilerPass(new WriteTransformerCompilerPass());
        $container->addCompilerPass(new ExporterCompilerPass());
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
}
