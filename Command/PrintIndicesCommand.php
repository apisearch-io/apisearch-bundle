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

use Apisearch\Model\Index;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PrintIndicesCommand.
 */
class PrintIndicesCommand extends WithAppRepositoryBucketCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'apisearch:print-indices';

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setDescription('Print all indices')
            ->addArgument(
                'app-name',
                InputArgument::REQUIRED,
                'App name'
            )
            ->addOption(
                'with-fields',
                null,
                InputOption::VALUE_NONE,
                'Print the fields'
            )
            ->addOption(
                'with-metadata',
                null,
                InputOption::VALUE_NONE,
                'Print the metadata'
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
        $appName = $input->getArgument('app-name');
        $indices = $this
            ->repositoryBucket
            ->findRepository($appName)
            ->getIndices();

        self::printIndices(
            $input,
            $output,
            $indices
        );
    }

    /**
     * Print indices.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param Index[]         $indices
     */
    public static function printIndices(
        InputInterface $input,
        OutputInterface $output,
        array $indices
    ) {
        $hasIndices = !empty($indices);
        $table = new Table($output);
        $headers = ['UUID', 'App ID', 'Doc Count', 'Size', 'Ok?', 'shards', 'replicas'];
        $withMetadata = $input->getOption('with-metadata');
        $withFields = $input->getOption('with-fields');
        if ($hasIndices && $withFields) {
            $headers[] = 'Fields';
        }

        if ($hasIndices && $withMetadata) {
            foreach ($indices[0]->getMetadata() as $field => $_) {
                $headers[] = ucfirst($field);
            }
        }

        $table->setHeaders($headers);

        /*
         * @var Index
         */
        foreach ($indices as $index) {
            $row = [
                $index->getUUID()->composeUUID(),
                $index->getAppUUID()->composeUUID(),
                $index->getDocCount(),
                $index->getSize(),
                $index->isOK()
                    ? 'Yes'
                    : 'No',
                $index->getShards(),
                $index->getReplicas(),
            ];

            if ($withFields) {
                $fields = $index->getFields();
                array_walk($fields, function (string &$type, string $field) {
                    $type = "$field: $type";
                });
                $row[] = implode("\n", $fields);
            }

            if ($withMetadata) {
                foreach ($index->getMetadata() as $_ => $value) {
                    $row[] = $value;
                }
            }

            $table->addRow($row);
        }
        $table->render();
    }

    /**
     * Dispatch domain event.
     *
     * @return string
     */
    protected static function getHeader(): string
    {
        return 'Print indices';
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
        return '';
    }
}
