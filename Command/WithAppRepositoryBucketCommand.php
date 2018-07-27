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

namespace Apisearch\Command;

use Apisearch\App\AppRepositoryBucket;
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
        $repository = $input->getArgument('repository');
        $configuration = $this->repositoryBucket->getConfiguration();

        if (!isset($configuration[$repository])) {
            throw new Exception(sprintf('Repository %s not found under apisearch configuration', $repository));
        }

        if (!$input->hasOption('index')) {
            return [$configuration[$repository]['app_id'], []];
        }

        $indexArray = $configuration[$repository]['indexes'] ?? [];

        $indices = array_map(function (string $index) use ($indexArray, $repository, $output) {
            if (!isset($indexArray[$index])) {
                throw new Exception(sprintf(
                    'Index %s not found under %s repository. Indices availables: %s',
                    $index,
                    $repository,
                    implode(', ', array_keys($indexArray))
                ));
            }

            return $indexArray[$index];
        }, $input->getOption('index'));

        return [$configuration[$repository]['app_id'], $indices];
    }
}
