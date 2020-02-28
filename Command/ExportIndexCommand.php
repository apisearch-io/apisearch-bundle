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
     * @var string
     */
    protected static $defaultName = 'apisearch:export-index';

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setDescription('Export your index')
            ->addArgument(
                'app-name',
                InputArgument::REQUIRED,
                'App name'
            )
            ->addArgument(
                'index-name',
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
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return mixed|null
     */
    protected function runCommand(InputInterface $input, OutputInterface $output)
    {
        $repository = $this->getRepository($input, $output);
        $file = $input->getArgument('file');

        self::exportToFile(
            $file,
            $output,
            function (Query $query) use ($repository) {
                return $repository->query($query);
            }
        );
    }

    /**
     * Save items and return total imported.
     *
     * @param string          $file
     * @param OutputInterface $output
     * @param callable        $queryItems
     *
     * @return int
     */
    public static function exportToFile(
        string $file,
        OutputInterface $output,
        callable $queryItems
    ) {
        $resource = fopen($file, 'w');

        $i = 1;
        $count = 0;
        while (true) {
            $items = $queryItems(Query::create('', $i, 500))->getItems();

            if (empty($items)) {
                break;
            }

            self::writeItemsToResource(
                $resource,
                $items
            );
            self::printPartialCountSaved($output, count($items));

            $count += count($items);
            ++$i;
        }

        fclose($resource);

        return $count;
    }

    /**
     * Echo items as CSV.
     *
     * @param resource $resource
     * @param Item[]   $items
     */
    private static function writeItemsToResource(
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

    /**
     * Print partial save.
     *
     * @param OutputInterface $output
     * @param int             $count
     */
    private static function printPartialCountSaved(
        OutputInterface $output,
        int $count
    ) {
        self::printInfoMessage(
            $output,
            self::getHeader(),
            sprintf('Partial export of %d items', $count)
        );
    }

    /**
     * Dispatch domain event.
     *
     * @return string
     */
    protected static function getHeader(): string
    {
        return 'Export index';
    }

    /**
     * Get success message.
     *
     * @param InputInterface $input
     * @param mixed          $result
     *
     * @return string
     */
    protected static function getSuccessMessage(
        InputInterface $input,
        $result
    ): string {
        return 'Index exported properly';
    }
}
