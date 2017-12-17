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

use Apisearch\Event\HttpEventRepository;
use Apisearch\Event\InMemoryEventRepository;
use Apisearch\Http\GuzzleClient;
use Apisearch\Http\TestClient;
use Apisearch\Repository\HttpRepository;
use Apisearch\Repository\InMemoryRepository;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Repository\TransformableRepository;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

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
        $repositoryConfigurations = $container->getParameter('apisearch.repository_configuration');
        foreach ($repositoryConfigurations as $name => $repositoryConfiguration) {
            foreach ($repositoryConfiguration['indexes'] as $index) {
                $this->createRepositoryReferenceServiceReference(
                    $container,
                    $name,
                    $repositoryConfiguration,
                    $index
                );

                $this->createClient(
                    $container,
                    $name,
                    $repositoryConfiguration,
                    $index
                );

                $this->createSearchRepository(
                    $container,
                    $name,
                    $repositoryConfiguration,
                    $index
                );

                $this->createEventRepository(
                    $container,
                    $name,
                    $repositoryConfiguration,
                    $index
                );
            }
        }
    }

    /**
     * Create client.
     *
     * @param ContainerBuilder $container
     * @param string           $name
     * @param array            $repositoryConfiguration
     * @param string           $index
     */
    private function createClient(
        ContainerBuilder $container,
        string $name,
        array $repositoryConfiguration,
        string $index
    ) {
        if ($repositoryConfiguration['http']) {
            $clientName = "apisearch.client_$name.$index";
            $repositoryConfiguration['test']
                ? $container
                    ->register($clientName, TestClient::class)
                    ->setArguments([
                        new Reference('test.client'),
                        $repositoryConfiguration['version'],
                    ])
                : $container
                    ->register($clientName, GuzzleClient::class)
                    ->setArguments([
                        $repositoryConfiguration['endpoint'],
                        $repositoryConfiguration['version'],
                    ]);
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
        array $repositoryConfiguration,
        string $index
    ) {
        $repositoryName = "apisearch.repository_$name.$index";
        $repositoryTransformableName = "apisearch.repository_transformable_$name.$index";
        $clientName = "apisearch.client_$name.$index";

        if (
            is_null($repositoryConfiguration['search']['repository_service']) ||
            ($repositoryConfiguration['search']['repository_service'] == $repositoryName)
        ) {
            $repoDefinition = $repositoryConfiguration['search']['in_memory']
                ? $container->register($repositoryName, InMemoryRepository::class)
                : $container
                    ->register($repositoryName, HttpRepository::class)
                    ->addArgument(new Reference($clientName))
                    ->addArgument($repositoryConfiguration['write_async']);
        } else {
            $container
                ->addAliases([
                    $repositoryName => $repositoryConfiguration['search']['repository_service'],
                ]);

            $repoDefinition = $container->getDefinition($repositoryConfiguration['search']['repository_service']);
        }

        $this->injectRepositoryCredentials(
            $repoDefinition,
            $name,
            $repositoryConfiguration,
            $index
        );

        $definition = $container
            ->register($repositoryTransformableName, TransformableRepository::class)
            ->setDecoratedService($repositoryName)
            ->addArgument(new Reference($repositoryTransformableName.'.inner'))
            ->addArgument(new Reference('apisearch.transformer'))
            ->setPublic(false);

        $this->injectRepositoryCredentials(
            $definition,
            $name,
            $repositoryConfiguration,
            $index
        );

        $container
            ->getDefinition('apisearch.repository_bucket')
            ->addMethodCall(
                'addRepository',
                [$name, $index, new Reference($repositoryName)]
            );
    }

    /**
     * Create event repository.
     *
     * @param ContainerBuilder $container
     * @param string           $name
     * @param array            $repositoryConfiguration
     * @param string           $index
     */
    private function createEventRepository(
        ContainerBuilder $container,
        string $name,
        array $repositoryConfiguration,
        string $index
    ) {
        $eventRepositoryName = "apisearch.event_repository_$name.$index";
        $clientName = "apisearch.client_$name.$index";

        if (
            is_null($repositoryConfiguration['event']['repository_service']) ||
            ($repositoryConfiguration['event']['repository_service'] == $eventRepositoryName)
        ) {
            $repositoryReferenceName = "apisearch.repository_reference.$name.$index";
            $repositoryReferenceReference = new Reference($repositoryReferenceName);
            $repositoryConfiguration['event']['in_memory']
                ? $container
                    ->register($eventRepositoryName, InMemoryEventRepository::class)
                    ->addMethodCall('setRepositoryReference', [
                        $repositoryReferenceReference,
                    ])
                : $container
                    ->register($eventRepositoryName, HttpEventRepository::class)
                    ->addArgument(new Reference($clientName))
                    ->addMethodCall('setCredentials', [
                        $repositoryReferenceReference,
                        $repositoryConfiguration['token'],
                    ]);
        } else {
            $repoDefinition = $container->getDefinition($repositoryConfiguration['event']['repository_service']);
            $this->injectRepositoryCredentials(
                $repoDefinition,
                $name,
                $repositoryConfiguration,
                $index
            );

            $container
                ->addAliases([
                    $eventRepositoryName => $repositoryConfiguration['event']['repository_service'],
                ]);
        }
    }

    /**
     * Inject credentials in repository.
     *
     * @param Definition $definition
     * @param string     $name
     * @param array      $repositoryConfiguration
     * @param string     $index
     */
    private function injectRepositoryCredentials(
        Definition $definition,
        string $name,
        array $repositoryConfiguration,
        string $index
    ) {
        if ($repositoryConfiguration['app_id']) {
            $repositoryReferenceName = "apisearch.repository_reference.$name.$index";
            $repositoryReferenceReference = new Reference($repositoryReferenceName);

            $repositoryConfiguration['token']
                ? $definition->addMethodCall('setCredentials', [
                    $repositoryReferenceReference,
                    $repositoryConfiguration['token'],
                ])
                : $definition->addMethodCall('setRepositoryReference', [
                    $repositoryReferenceReference,
                ]);
        }
    }

    /**
     * Crate Repository Reference service reference.
     *
     * @param ContainerBuilder $container
     * @param string           $name
     * @param array            $repositoryConfiguration
     * @param string           $index
     *
     * @return Definition
     */
    private function createRepositoryReferenceServiceReference(
        ContainerBuilder $container,
        string $name,
        array $repositoryConfiguration,
        string $index
    ): Definition {
        $repositoryReferenceName = "apisearch.repository_reference.$name.$index";
        $reference = $container->register($repositoryReferenceName, RepositoryReference::class);
        $reference
            ->setPublic(false)
            ->setFactory([
                RepositoryReference::class,
                'create',
            ])
            ->addArgument($repositoryConfiguration['app_id'])
            ->addArgument($index);

        return $reference;
    }
}
