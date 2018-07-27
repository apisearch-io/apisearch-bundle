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

use Apisearch\Model\Coordinate;
use Apisearch\Model\Item;
use Apisearch\Query\Query;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ExportIndexCommand.
 */
class ExportIndexCommand extends WithRepositoryBucketCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('apisearch:export-index')
            ->setDescription('Export your index')
            ->addArgument(
                'repository',
                InputArgument::REQUIRED,
                'Repository name'
            )
            ->addArgument(
                'index',
                InputArgument::REQUIRED,
                'Index name'
            )
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'File'
            );
    }

    /**
     * Dispatch domain event.
     *
     * @return string
     */
    protected function getHeader(): string
    {
        return 'Export index';
    }

    /**
     * Dispatch domain event.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return mixed|null
     */
    protected function runCommand(InputInterface $input, OutputInterface $output)
    {
        $repositoryName = $input->getArgument('repository');
        $indexName = $input->getArgument('index');
        $file = $input->getArgument('file');
        $resource = fopen($file, 'w');

        $i = 0;
        while (true) {
            $items = $this
                ->repositoryBucket
                ->findRepository(
                    $repositoryName,
                    $indexName
                )
                ->query(Query::create('', $i, 100))
                ->getItems();

            if (empty($items)) {
                return;
            }

            $this->writeItemsToResource(
                $resource,
                $items
            );

            ++$i;
        }

        fclose($resource);
    }

    /**
     * Get success message.
     *
     * @param InputInterface $input
     * @param mixed          $result
     *
     * @return string
     */
    protected function getSuccessMessage(
        InputInterface $input,
        $result
    ): string {
        return 'Index exported properly';
    }

    /**
     * Echo items as CSV.
     *
     * @param resource $resource
     * @param Item[]   $items
     */
    private function writeItemsToResource(
        $resource,
        array $items
    ) {
        foreach ($items as $item) {
            fputcsv($resource, [
                $item->getId(),
                $item->getType(),
                json_encode($item->getMetadata()),
                json_encode($item->getIndexedMetadata()),
                json_encode($item->getSearchableMetadata()),
                json_encode($item->getExactMatchingMetadata()),
                json_encode($item->getSuggest()),
                json_encode(
                    ($item->getCoordinate() instanceof Coordinate)
                        ? $item->getCoordinate()->toArray()
                        : null
                ),
            ]);
        }
    }
}
