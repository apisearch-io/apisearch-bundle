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

use Apisearch\Repository\Repository;
use Apisearch\Repository\RepositoryBucket;
use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class WithRepositoryBucketCommand.
 */
abstract class WithRepositoryBucketCommand extends ApisearchFormattedCommand
{
    /**
     * @var RepositoryBucket
     *
     * Repository bucket
     */
    protected $repositoryBucket;

    /**
     * ResetIndexCommand constructor.
     *
     * @param RepositoryBucket $repositoryBucket
     */
    public function __construct(RepositoryBucket $repositoryBucket)
    {
        parent::__construct();

        $this->repositoryBucket = $repositoryBucket;
    }

    /**
     * Get repository and index.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return Repository
     *
     * @throws Exception
     */
    protected function getRepository(
        InputInterface $input,
        OutputInterface $output
    ): Repository {
        $appName = $input->getArgument('app-name');
        $indexName = $input->getArgument('index-name');
        $repository = $this
            ->repositoryBucket
            ->findRepository(
                $appName,
                $indexName
            );

        if (is_null($repository)) {
            throw new Exception(sprintf('App %s not found under apisearch configuration', $appName));
        }

        $appId = $repository->getAppUUID()->composeUUID();
        self::printInfoMessage(
            $output,
            $this->getHeader(),
            "App: <strong>{$appName} / {$appId}</strong>"
        );

        $indexId = $repository->getIndexUUID()->composeUUID();
        self::printInfoMessage(
            $output,
            $this->getHeader(),
            "Index: <strong>{$indexName} / {$indexId}</strong>"
        );

        return $repository;
    }
}
