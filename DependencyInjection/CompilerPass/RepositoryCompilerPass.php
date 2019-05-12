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

namespace Apisearch\DependencyInjection\CompilerPass;

use Apisearch\App\DiskAppRepository;
use Apisearch\App\HttpAppRepository;
use Apisearch\App\InMemoryAppRepository;
use Apisearch\Http\RetryMap;
use Apisearch\Http\TCPClient;
use Apisearch\Http\TestClient;
use Apisearch\Model\AppUUID;
use Apisearch\Model\IndexUUID;
use Apisearch\Model\TokenUUID;
use Apisearch\Repository\DiskRepository;
use Apisearch\Repository\HttpRepository;
use Apisearch\Repository\InMemoryRepository;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Repository\TransformableRepository;
use Apisearch\User\DiskUserRepository;
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

            foreach ($repositoryConfiguration['indices'] as $indexName => $indexId) {
                $indexId = (string) $indexId;
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

        $this->createClientRetryMap(
            $container,
            $name,
            $repositoryConfiguration
        );

        $this->createClient(
            $container,
            $name,
            $repositoryConfiguration
        );

        $this->createStandardRepository(
            $container,
            $name,
            $repositoryConfiguration,
            '',
            'app',
            InMemoryAppRepository::class,
            DiskAppRepository::class,
            HttpAppRepository::class
        );

        $this->createStandardRepository(
            $container,
            $name,
            $repositoryConfiguration,
            '',
            'user',
            InMemoryUserRepository::class,
            DiskUserRepository::class,
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

        $this->createSearchRepository(
            $container,
            $name,
            $repositoryConfiguration,
            $indexName
        );
    }

    /**
     * Create client retry map.
     *
     * @param ContainerBuilder $container
     * @param string           $name
     * @param string           $name
     * @param array            $repositoryConfiguration
     */
    private function createClientRetryMap(
        ContainerBuilder $container,
        string $name,
        array $repositoryConfiguration
    ) {
        if (!$this->repositoryIsHttp($repositoryConfiguration)) {
            return;
        }

        $clientRetryMapName = rtrim("apisearch.retry_map_$name", '.');
        $container
                ->register($clientRetryMapName, RetryMap::class)
                ->setFactory([
                    RetryMap::class,
                    'createFromArray',
                ])
                ->setArgument(0, $repositoryConfiguration['http']['retry_map']);
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
        if (!$this->repositoryIsHttp($repositoryConfiguration)) {
            return;
        }

        $clientName = rtrim("apisearch.client_$name", '.');
        $clientRetryMapName = rtrim("apisearch.retry_map_$name", '.');
        ('http_test' === $repositoryConfiguration['adapter'])
            ? $container
                ->register($clientName, TestClient::class)
                ->setArguments([
                    new Reference('kernel'),
                    $repositoryConfiguration['version'],
                    new reference($clientRetryMapName),
                ])
                ->setPublic($this->repositoryIsTest($repositoryConfiguration))
            : $container
                ->register($clientName, TCPClient::class)
                ->setArguments([
                    $repositoryConfiguration['endpoint'],
                    new Reference('apisearch.http_adapter'),
                    $repositoryConfiguration['version'],
                    new reference($clientRetryMapName),
                ])
                ->setPublic($this->repositoryIsTest($repositoryConfiguration));
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
        $clientName = "apisearch.client_$name";

        switch ($repositoryConfiguration['adapter']) {
            case 'in_memory':
                $repositoryDefinition = $container
                    ->register($repositoryName, InMemoryRepository::class)
                    ->setPublic($this->repositoryIsTest($repositoryConfiguration));
                break;

            case 'disk':
                $repositoryDefinition = $container
                    ->register($repositoryName, DiskRepository::class)
                    ->addArgument($repositoryConfiguration['disk_file'].'.'.$name)
                    ->setPublic($this->repositoryIsTest($repositoryConfiguration));
                break;

            case 'http':
            case 'http_test':
                $repositoryDefinition = $container
                    ->register($repositoryName, HttpRepository::class)
                    ->addArgument(new Reference($clientName))
                    ->setPublic($this->repositoryIsTest($repositoryConfiguration));
                break;

            case 'service':
                $container->setAlias($repositoryName, $repositoryConfiguration['search']['repository_service']);
                $aliasDefinition = $container->getAlias($repositoryName);
                $aliasDefinition->setPublic($this->repositoryIsTest($repositoryConfiguration));
                $repositoryDefinition = $container->getDefinition($repositoryConfiguration['search']['repository_service']);
                break;
        }

        $this->injectRepositoryCredentials(
            $repositoryDefinition,
            $name,
            $repositoryConfiguration,
            $indexName
        );

        $definition = $container
            ->register($repositoryTransformableName, TransformableRepository::class)
            ->setDecoratedService($repositoryName)
            ->addArgument(new Reference($repositoryTransformableName.'.inner'))
            ->addArgument(new Reference('apisearch.transformer'))
            ->setPublic($this->repositoryIsTest($repositoryConfiguration));

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
            )
            ->setPublic($this->repositoryIsTest($repositoryConfiguration));
    }

    /**
     * Create standard repository.
     *
     * @param ContainerBuilder $container
     * @param string           $appName
     * @param array            $repositoryConfiguration
     * @param string           $indexName
     * @param string           $prefix
     * @param string           $inMemoryRepositoryNamespace
     * @param string           $diskRepositoryNamespace
     * @param string           $httpRepositoryNamespace
     */
    private function createStandardRepository(
        ContainerBuilder $container,
        string $appName,
        array $repositoryConfiguration,
        string $indexName,
        string $prefix,
        string $inMemoryRepositoryNamespace,
        string $diskRepositoryNamespace,
        string $httpRepositoryNamespace
    ) {
        $repositoryName = rtrim("apisearch.{$prefix}_repository_$appName.$indexName", '.');
        $tokenUUIDName = rtrim("apisearch.token_uuid.$appName");
        $clientName = rtrim("apisearch.client_$appName", '.');

        $tokenUUIDReference = $container->register($tokenUUIDName, TokenUUID::class);
        $tokenUUIDReference
            ->setFactory([
                TokenUUID::class,
                'createById',
            ])
            ->addArgument((string) $repositoryConfiguration['token'])
            ->setPrivate(true);

        $repositoryReferenceName = rtrim("apisearch.repository_reference.$appName.$indexName", '.');
        $repositoryReferenceReference = new Reference($repositoryReferenceName);

        switch ($repositoryConfiguration['adapter']) {
            case 'in_memory':
                $container
                    ->register($repositoryName, $inMemoryRepositoryNamespace)
                    ->addMethodCall('setRepositoryReference', [
                        $repositoryReferenceReference,
                    ])
                    ->setPublic($this->repositoryIsTest($repositoryConfiguration));
                break;

            case 'disk':
                $container
                    ->register($repositoryName, $diskRepositoryNamespace)
                    ->addMethodCall('setRepositoryReference', [
                        $repositoryReferenceReference,
                    ])
                    ->addArgument($repositoryConfiguration['disk_file'].'.'.$prefix.'.'.$appName)
                    ->setPublic($this->repositoryIsTest($repositoryConfiguration));
                break;

            case 'http':
            case 'http_test':
                $container
                    ->register($repositoryName, $httpRepositoryNamespace)
                    ->addArgument(new Reference($clientName))
                    ->addMethodCall('setCredentials', [
                        $repositoryReferenceReference,
                        new Reference($tokenUUIDName),
                    ])
                    ->setPublic($this->repositoryIsTest($repositoryConfiguration));
                break;

            case 'service':
                $repoDefinition = $container->getDefinition($repositoryConfiguration[$prefix]['repository_service']);
                $this->injectRepositoryCredentials(
                    $repoDefinition,
                    $appName,
                    $repositoryConfiguration,
                    $indexName
                );

                $container->setAlias($repositoryName, $repositoryConfiguration[$prefix]['repository_service']);
                $aliasDefinition = $container->getAlias($repositoryName);
                $aliasDefinition->setPublic($this->repositoryIsTest($repositoryConfiguration));
        }

        $container
            ->getDefinition("apisearch.{$prefix}_repository_bucket")
            ->addMethodCall(
                'addRepository',
                [$appName, new Reference($repositoryName)]
            )
            ->setPublic($this->repositoryIsTest($repositoryConfiguration));
    }

    /**
     * Inject credentials in repository.
     *
     * @param Definition $definition
     * @param string     $appName
     * @param array      $repositoryConfiguration
     * @param string     $indexName
     */
    private function injectRepositoryCredentials(
        Definition $definition,
        string $appName,
        array $repositoryConfiguration,
        string $indexName
    ) {
        if ($repositoryConfiguration['app_id']) {
            $repositoryReferenceName = rtrim("apisearch.repository_reference.$appName.$indexName", '.');
            $tokenUUIDName = rtrim("apisearch.token_uuid.$appName");
            $repositoryReferenceReference = new Reference($repositoryReferenceName);

            $repositoryConfiguration['token']
                ? $definition->addMethodCall('setCredentials', [
                    $repositoryReferenceReference,
                    new Reference($tokenUUIDName),
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
        $appUUIDReference = $container->register("apisearch.app_uuid.$appName", AppUUID::class);
        $appUUIDReference
            ->setFactory([
                AppUUID::class,
                'createById',
            ])
            ->addArgument((string) $repositoryConfiguration['app_id'])
            ->setPrivate(true);

        $indexUUIDReference = $container->register("apisearch.index_uuid.$appName.$indexName", IndexUUID::class);
        $indexUUIDReference
            ->setFactory([
                IndexUUID::class,
                'createById',
            ])
            ->addArgument((string) $indexId)
            ->setPrivate(true);

        $reference = $container->register($repositoryReferenceName, RepositoryReference::class);
        $reference
            ->setFactory([
                RepositoryReference::class,
                'create',
            ])
            ->addArgument(new Reference("apisearch.app_uuid.$appName"))
            ->addArgument(new Reference("apisearch.index_uuid.$appName.$indexName"))
            ->setPublic($this->repositoryIsTest($repositoryConfiguration));

        return $reference;
    }

    /**
     * Is test.
     *
     * @param array $repositoryConfiguration
     *
     * @return bool
     */
    private function repositoryIsTest(array $repositoryConfiguration)
    {
        return $repositoryConfiguration['test'];
    }

    /**
     * Is http.
     *
     * @param array $repositoryConfiguration
     *
     * @return bool
     */
    private function repositoryIsHttp(array $repositoryConfiguration)
    {
        return in_array($repositoryConfiguration['adapter'], ['http', 'http_test']);
    }
}
