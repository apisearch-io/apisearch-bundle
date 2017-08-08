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

namespace Puntmig\Search\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

use Puntmig\Search\Http\GuzzleClient;
use Puntmig\Search\Http\TestClient;
use Puntmig\Search\Repository\HttpRepository;
use Puntmig\Search\Repository\TransformableRepository;

/**
 * File header placeholder.
 */
class RepositoryCompilerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $repositoryConfigurations = $container->getParameter('puntmig_search.repository_configuration');
        foreach ($repositoryConfigurations as $name => $repositoryConfiguration) {
            $this->createSearchRepository(
                $container,
                $name,
                $repositoryConfiguration
            );
        }
    }

    /**
     * Create a repository by connection configuration.
     *
     * @param ContainerBuilder $container
     * @param string           $name
     * @param array            $repositoryConfiguration
     */
    private function createSearchRepository(
        ContainerBuilder $container,
        string $name,
        array $repositoryConfiguration
    ) {
        $repositoryConfiguration['test']
            ? $container
                ->register('puntmig_search.client_' . $name, TestClient::class)
                ->addArgument(new Reference('test.client'))
        : $container
                ->register('puntmig_search.client_' . $name, GuzzleClient::class)
                ->addArgument($repositoryConfiguration['endpoint']);

        (
            is_null($repositoryConfiguration['repository_service']) ||
            ('puntmig_search.repository_' . $name == $repositoryConfiguration['repository_service'])
        )
            ? $container
                ->register('puntmig_search.repository_' . $name, HttpRepository::class)
                ->addArgument(new Reference('puntmig_search.client_' . $name))
                ->addMethodCall('setKey', [$repositoryConfiguration['secret']])
            : $container
                ->addAliases([
                    'puntmig_search.repository_' . $name => $repositoryConfiguration['repository_service']
                ]);

        $container
            ->register('puntmig_search.repository_transformable_' . $name, TransformableRepository::class)
            ->setDecoratedService('puntmig_search.repository_' . $name)
            ->addArgument(new Reference('puntmig_search.repository_transformable_' . $name . '.inner'))
            ->addArgument(new Reference('puntmig_search.transformer'))
            ->addMethodCall('setKey', [$repositoryConfiguration['secret']])
            ->setPublic(false);

        $container
            ->getDefinition('puntmig_search.repository_bucket')
            ->addMethodCall(
                'addRepository',
                [$name, new Reference('puntmig_search.repository_' . $name)]
            );
    }
}
