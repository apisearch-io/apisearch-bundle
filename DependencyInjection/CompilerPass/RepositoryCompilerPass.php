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

use Apisearch\App\HttpAppRepository;
use Apisearch\App\InMemoryAppRepository;
use Apisearch\Event\HttpEventRepository;
use Apisearch\Event\InMemoryEventRepository;
use Apisearch\Http\GuzzleClient;
use Apisearch\Http\TestClient;
use Apisearch\Log\HttpLogRepository;
use Apisearch\Log\InMemoryLogRepository;
use Apisearch\Repository\HttpRepository;
use Apisearch\Repository\InMemoryRepository;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Repository\TransformableRepository;
use Apisearch\User\HttpUserRepository;
use Apisearch\User\InMemoryUserRepository;
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
            $this->createAppRepositories(
                $container,
                $name,
                $repositoryConfiguration
            );

            foreach ($repositoryConfiguration['indexes'] as $indexName => $indexId) {
                $this->createIndexRepositories(
                    $container,
                    $name,
                    $repositoryConfiguration,
                    $indexId,
                    $indexName
                );
            }
        }
    }

    /**
     * Create app repositories.
     *
     * @param ContainerBuilder $container
     * @param string           $name
     * @param array            $repositoryConfiguration
     */
    private function createAppRepositories(
        ContainerBuilder $container,
        string $name,
        array $repositoryConfiguration
    ) {
        $this->createRepositoryReferenceServiceReference(
            $container,
            $name,
            $repositoryConfiguration,
            '',
            ''
        );

        $this->createClient(
            $container,
            $name,
            $repositoryConfiguration,
            ''
        );

        $this->createStandardRepository(
            $container,
            $name,
            $repositoryConfiguration,
            '',
            'app',
            InMemoryAppRepository::class,
            HttpAppRepository::class
        );

        $this->createStandardRepository(
            $container,
            $name,
            $repositoryConfiguration,
            '',
            'user',
            InMemoryUserRepository::class,
            HttpUserRepository::class
        );
    }

    /**
     * Create app repositories.
     *
     * @param ContainerBuilder $container
     * @param string           $name
     * @param array            $repositoryConfiguration
     * @param string           $indexId
     * @param string           $indexName
     */
    private function createIndexRepositories(
        ContainerBuilder $container,
        string $name,
        array $repositoryConfiguration,
        $indexId,
        $indexName
    ) {
        $this->createRepositoryReferenceServiceReference(
            $container,
            $name,
            $repositoryConfiguration,
            $indexId,
            $indexName
        );

        $this->createClient(
            $container,
            $name,
            $repositoryConfiguration,
            $indexName
        );

        $this->createSearchRepository(
            $container,
            $name,
            $repositoryConfiguration,
            $indexName
        );

        $this->createStandardRepository(
            $container,
            $name,
            $repositoryConfiguration,
            $indexName,
            'event',
            InMemoryEventRepository::class,
            HttpEventRepository::class
        );

        $this->createStandardRepository(
            $container,
            $name,
            $repositoryConfiguration,
            $indexName,
            'log',
            InMemoryLogRepository::class,
            HttpLogRepository::class
        );
    }

    /**
     * Create client.
     *
     * @param ContainerBuilder $container
     * @param string           $name
     * @param array            $repositoryConfiguration
     * @param string           $indexName
     */
    private function createClient(
        ContainerBuilder $container,
        string $name,
        array $repositoryConfiguration,
        string $indexName
    ) {
        if ($repositoryConfiguration['http']) {
            $clientName = rtrim("apisearch.client_$name.$indexName", '.');
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
     * @param string           $indexName
     */
    private function createSearchRepository(
        ContainerBuilder $container,
        string $name,
        array $repositoryConfiguration,
        string $indexName
    ) {
        $repositoryName = "apisearch.repository_$name.$indexName";
        $repositoryTransformableName = "apisearch.repository_transformable_$name.$indexName";
        $clientName = "apisearch.client_$name.$indexName";

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
            $indexName
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
            $indexName
        );

        $container
            ->getDefinition('apisearch.repository_bucket')
            ->addMethodCall(
                'addRepository',
                [$name, $indexName, new Reference($repositoryName)]
            );
    }

    /**
     * Create standard repository.
     *
     * @param ContainerBuilder $container
     * @param string           $name
     * @param array            $repositoryConfiguration
     * @param string           $indexName
     * @param string           $prefix
     * @param string           $inMemoryRepositoryNamespace
     * @param string           $httpRepositoryNamespace
     */
    private function createStandardRepository(
        ContainerBuilder $container,
        string $name,
        array $repositoryConfiguration,
        string $indexName,
        string $prefix,
        string $inMemoryRepositoryNamespace,
        string $httpRepositoryNamespace
    ) {
        $repositoryName = rtrim("apisearch.{$prefix}_repository_$name.$indexName", '.');
        $clientName = rtrim("apisearch.client_$name.$indexName", '.');

        if (
            is_null($repositoryConfiguration[$prefix]['repository_service']) ||
            ($repositoryConfiguration[$prefix]['repository_service'] == $repositoryName)
        ) {
            $repositoryReferenceName = rtrim("apisearch.repository_reference.$name.$indexName", '.');
            $repositoryReferenceReference = new Reference($repositoryReferenceName);
            $repositoryConfiguration[$prefix]['in_memory']
                ? $container
                    ->register($repositoryName, $inMemoryRepositoryNamespace)
                    ->addMethodCall('setRepositoryReference', [
                        $repositoryReferenceReference,
                    ])
                : $container
                    ->register($repositoryName, $httpRepositoryNamespace)
                    ->addArgument(new Reference($clientName))
                    ->addMethodCall('setCredentials', [
                        $repositoryReferenceReference,
                        $repositoryConfiguration['token'],
                    ]);
        } else {
            $repoDefinition = $container->getDefinition($repositoryConfiguration[$prefix]['repository_service']);
            $this->injectRepositoryCredentials(
                $repoDefinition,
                $name,
                $repositoryConfiguration,
                $indexName
            );

            $container
                ->addAliases([
                    $repositoryName => $repositoryConfiguration[$prefix]['repository_service'],
                ]);
        }
    }

    /**
     * Inject credentials in repository.
     *
     * @param Definition $definition
     * @param string     $name
     * @param array      $repositoryConfiguration
     * @param string     $indexName
     */
    private function injectRepositoryCredentials(
        Definition $definition,
        string $name,
        array $repositoryConfiguration,
        string $indexName
    ) {
        if ($repositoryConfiguration['app_id']) {
            $repositoryReferenceName = rtrim("apisearch.repository_reference.$name.$indexName", '.');
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
     * @param string           $appName
     * @param array            $repositoryConfiguration
     * @param string           $indexId
     * @param string           $indexName
     *
     * @return Definition
     */
    private function createRepositoryReferenceServiceReference(
        ContainerBuilder $container,
        string $appName,
        array $repositoryConfiguration,
        string $indexId,
        string $indexName
    ): Definition {
        $repositoryReferenceName = rtrim("apisearch.repository_reference.$appName.$indexName", '.');
        $reference = $container->register($repositoryReferenceName, RepositoryReference::class);
        $reference
            ->setPublic(false)
            ->setFactory([
                RepositoryReference::class,
                'create',
            ])
            ->addArgument($repositoryConfiguration['app_id'])
            ->addArgument($indexId);

        return $reference;
    }
}
