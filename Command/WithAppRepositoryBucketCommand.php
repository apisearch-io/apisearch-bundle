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

namespace Apisearch\Command;

use Apisearch\App\AppRepositoryBucket;
use Apisearch\Model\AppUUID;
use Apisearch\Model\IndexUUID;
use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class WithAppRepositoryBucketCommand.
 */
abstract class WithAppRepositoryBucketCommand extends ApisearchFormattedCommand
{
    /**
     * @var AppRepositoryBucket
     *
     * Repository bucket
     */
    protected $repositoryBucket;

    /**
     * WithAppRepositoryBucketCommand constructor.
     *
     * @param AppRepositoryBucket $repositoryBucket
     */
    public function __construct(AppRepositoryBucket $repositoryBucket)
    {
        parent::__construct();

        $this->repositoryBucket = $repositoryBucket;
    }

    /**
     * Check repository and indices existence.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return array
     *
     * @throws Exception
     */
    protected function getRepositoryAndIndices(
        InputInterface $input,
        OutputInterface $output
    ): array {
        $appName = $input->getArgument('app-name');
        $configuration = $this->repositoryBucket->getConfiguration();

        if (!isset($configuration[$appName])) {
            throw new Exception(sprintf('App %s not found under apisearch configuration', $appName));
        }

        $appId = $configuration[$appName]['app_id'];
        self::printInfoMessage(
            $output,
            $this->getHeader(),
            "App: <strong>{$appName} / {$appId}</strong>"
        );

        if (!$input->hasOption('index-name')) {
            return [$configuration[$appName]['app_id'], []];
        }

        $indexNamesAsArray = $configuration[$appName]['indices'] ?? [];

        $indices = array_map(function (string $indexName) use ($indexNamesAsArray, $appName, $output) {
            if (!isset($indexNamesAsArray[$indexName])) {
                throw new Exception(sprintf(
                    'Index %s not found under %s repository. Indices availables: %s',
                    $indexName,
                    $appName,
                    implode(', ', array_keys($indexNamesAsArray))
                ));
            }

            $indexId = $indexNamesAsArray[$indexName];
            self::printInfoMessage(
                $output,
                $this->getHeader(),
                "Index: <strong>{$indexName} / {$indexId}</strong>"
            );

            return IndexUUID::createById($indexId);
        }, $input->getOption('index-name'));

        return [
            AppUUID::createById($appId),
            $indices,
        ];
    }

    /**
     * Get repository and index.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return array
     *
     * @throws Exception
     */
    protected function getRepositoryAndIndex(
        InputInterface $input,
        OutputInterface $output
    ): array {
        $appName = $input->getArgument('app-name');
        $indexName = $input->getArgument('index-name');
        $configuration = $this->repositoryBucket->getConfiguration();

        if (!isset($configuration[$appName])) {
            throw new Exception(sprintf('App %s not found under apisearch configuration', $appName));
        }

        $appId = $configuration[$appName]['app_id'];
        self::printInfoMessage(
            $output,
            $this->getHeader(),
            "App: <strong>{$appName} / {$appId}</strong>"
        );

        $indexNamesAsArray = $configuration[$appName]['indices'] ?? [];
        if (!isset($indexNamesAsArray[$indexName])) {
            throw new Exception(sprintf(
                'Index %s not found under %s repository. Indices availables: %s',
                $indexName,
                $appName,
                implode(', ', array_keys($indexNamesAsArray))
            ));
        }

        $indexId = $indexNamesAsArray[$indexName];
        self::printInfoMessage(
            $output,
            $this->getHeader(),
            "Index: <strong>{$indexName} / {$indexId}</strong>"
        );

        return [
            AppUUID::createById($appId),
            IndexUUID::createById($indexId),
        ];
    }
}
