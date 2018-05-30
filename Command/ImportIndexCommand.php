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

use Apisearch\Model\Item;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ImportIndexCommand.
 */
class ImportIndexCommand extends WithRepositoryBucketCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('apisearch:import-index')
            ->setDescription('Import your index')
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
        return 'Import index';
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
        $repository = $this
            ->repositoryBucket
            ->findRepository(
                $repositoryName,
                $indexName
            );

        if (false !== ($handle = fopen($file, 'r'))) {
            while (false !== ($data = fgetcsv($handle, 0, ','))) {
                $itemAsArray = [
                    'uuid' => [
                        'id' => $data[0],
                        'type' => $data[1],
                    ],
                    'metadata' => json_decode($data[2], true),
                    'indexed_metadata' => json_decode($data[3], true),
                    'searchable_metadata' => json_decode($data[4], true),
                    'exact_matching_metadata' => json_decode($data[5], true),
                    'suggest' => json_decode($data[6], true),
                ];

                if (is_array($data[7])) {
                    $itemAsArray['coordinate'] = $data[7];
                }

                $item = Item::createFromArray($itemAsArray);
                $repository->addItem($item);
                $repository->flush(500, true);
            }
            $repository->flush(500, false);
            fclose($handle);
        }
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
        return 'Index imported properly';
    }
}
