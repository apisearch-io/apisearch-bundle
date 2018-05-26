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

namespace Apisearch\Command;

use Apisearch\Model\Coordinate;
use Apisearch\Model\Item;
use Apisearch\Query\Query;
use Apisearch\Repository\RepositoryBucket;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ExportIndexCommand.
 */
class ExportIndexCommand extends ApisearchCommand
{
    /**
     * @var RepositoryBucket
     *
     * Repository bucket
     */
    private $repositoryBucket;

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
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
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
                ->query(Query::create('', $i, 10000))
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
