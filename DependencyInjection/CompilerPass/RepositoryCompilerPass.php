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

use Puntmig\Search\Event\HttpEventRepository;
use Puntmig\Search\Event\InMemoryEventRepository;
use Puntmig\Search\Http\GuzzleClient;
use Puntmig\Search\Http\TestClient;
use Puntmig\Search\Repository\HttpRepository;
use Puntmig\Search\Repository\InMemoryRepository;
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
            $this->createClient(
                $container,
                $name,
                $repositoryConfiguration
            );

            $this->createSearchRepository(
                $container,
                $name,
                $repositoryConfiguration
            );

            $this->createEventRepository(
                $container,
                $name,
                $repositoryConfiguration
            );
        }
    }

    /**
     * Create client.
     *
     * @param ContainerBuilder $container
     * @param string           $name
     * @param array            $repositoryConfiguration
     */
    private function createClient(
        ContainerBuilder $container,
        string $name,
        array $repositoryConfiguration
    ) {
        if ($repositoryConfiguration['http']) {
            $repositoryConfiguration['test']
                ? $container
                    ->register('puntmig_search.client_'.$name, TestClient::class)
                    ->addArgument(new Reference('test.client'))
                : $container
                    ->register('puntmig_search.client_'.$name, GuzzleClient::class)
                    ->addArgument($repositoryConfiguration['endpoint']);
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
        if (
            is_null($repositoryConfiguration['search']['repository_service']) ||
            ($repositoryConfiguration['search']['repository_service'] == 'puntmig_search.repository_'.$name)
        ) {
            $repoDefinition = $repositoryConfiguration['search']['in_memory']
                ? $container->register('puntmig_search.repository_'.$name, InMemoryRepository::class)
                : $container
                    ->register('puntmig_search.repository_'.$name, HttpRepository::class)
                    ->addArgument(new Reference('puntmig_search.client_'.$name))
                    ->addArgument($repositoryConfiguration['write_async']);
        } else {
            $container
                ->addAliases([
                    'puntmig_search.repository_'.$name => $repositoryConfiguration['search']['repository_service'],
                ]);

            $repoDefinition = $container->getDefinition($repositoryConfiguration['search']['repository_service']);
        }

        if ($repositoryConfiguration['secret']) {
            $repoDefinition->addMethodCall('setAppId', [$repositoryConfiguration['app_id']]);
            $repoDefinition->addMethodCall('setKey', [$repositoryConfiguration['secret']]);
        }

        $definition = $container
            ->register('puntmig_search.repository_transformable_'.$name, TransformableRepository::class)
            ->setDecoratedService('puntmig_search.repository_'.$name)
            ->addArgument(new Reference('puntmig_search.repository_transformable_'.$name.'.inner'))
            ->addArgument(new Reference('puntmig_search.transformer'))
            ->setPublic(false);

        if ($repositoryConfiguration['secret']) {
            $definition->addMethodCall('setAppId', [$repositoryConfiguration['app_id']]);
            $definition->addMethodCall('setKey', [$repositoryConfiguration['secret']]);
        }

        $container
            ->getDefinition('puntmig_search.repository_bucket')
            ->addMethodCall(
                'addRepository',
                [$name, new Reference('puntmig_search.repository_'.$name)]
            );
    }

    /**
     * Create event repository.
     *
     * @param ContainerBuilder $container
     * @param string           $name
     * @param array            $repositoryConfiguration
     */
    private function createEventRepository(
        ContainerBuilder $container,
        string $name,
        array $repositoryConfiguration
    ) {
        (
            is_null($repositoryConfiguration['event']['repository_service']) ||
            ($repositoryConfiguration['event']['repository_service'] == 'puntmig_search.event_repository_'.$name)
        )
            ?
                (
                    $repositoryConfiguration['event']['in_memory']
                        ? $container->register('puntmig_search.event_repository_'.$name, InMemoryEventRepository::class)
                        : $container
                            ->register('puntmig_search.event_repository_'.$name, HttpEventRepository::class)
                            ->addArgument(new Reference('puntmig_search.client_'.$name))
                )
            : $container
                ->addAliases([
                    'puntmig_search.event_repository_'.$name => $repositoryConfiguration['event']['repository_service'],
                ]);
    }
}
