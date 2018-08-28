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

use Apisearch\Config\ImmutableConfig;
use Apisearch\Exception\ResourceNotAvailableException;
use Apisearch\Model\IndexUUID;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateIndexCommand.
 */
class CreateIndexCommand extends WithAppRepositoryBucketCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('apisearch:create-index')
            ->setDescription('Create an index')
            ->addArgument(
                'app-name',
                InputArgument::REQUIRED,
                'App name'
            )
            ->addArgument(
                'index',
                InputArgument::REQUIRED,
                'Index'
            )
            ->addOption(
                'language',
                null,
                InputOption::VALUE_OPTIONAL,
                'Index language',
                null
            )
            ->addOption(
                'no-store-searchable-metadata',
                null,
                InputOption::VALUE_NONE,
                'Store searchable metadata'
            )
            ->addOption(
                'synonym',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Synonym'
            );
    }

    /**
     * Dispatch domain event.
     *
     * @return string
     */
    protected function getHeader(): string
    {
        return 'Create index';
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
        $index = $input->getArgument('index');

        try {
            $this
                ->repositoryBucket
                ->findRepository($appName)
                ->createIndex(
                    new IndexUUID($index),
                    ImmutableConfig::createFromArray([
                        'language' => $input->getOption('language'),
                        'store_searchable_metadata' => !$input->getOption('no-store-searchable-metadata'),
                        'synonyms' => array_map(function (string $synonym) {
                            return ['words' => array_map('trim', explode(',', $synonym))];
                        }, $input->getOption('synonym')),
                    ])
                );
        } catch (ResourceNotAvailableException $exception) {
            $this->printInfoMessage(
                $output,
                $this->getHeader(),
                'Index is already created. Skipping.'
            );
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
        return 'Indices created properly';
    }
}
