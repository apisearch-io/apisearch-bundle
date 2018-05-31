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

use Apisearch\Config\ImmutableConfig;
use Apisearch\Event\EventRepositoryBucket;
use Apisearch\Exception\ResourceNotAvailableException;
use Apisearch\Log\LogRepositoryBucket;
use Apisearch\Repository\RepositoryBucket;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateIndexCommand.
 */
class CreateIndexCommand extends WithRepositoryBucketCommand
{
    /**
     * @var EventRepositoryBucket
     *
     * Event repository bucket
     */
    private $eventRepositoryBucket;

    /**
     * @var LogRepositoryBucket
     *
     * Log repository bucket
     */
    private $logRepositoryBucket;

    /**
     * CreateIndexCommand constructor.
     *
     * @param RepositoryBucket      $repositoryBucket
     * @param EventRepositoryBucket $eventRepositoryBucket
     * @param LogRepositoryBucket   $logRepositoryBucket
     */
    public function __construct(
        RepositoryBucket $repositoryBucket,
        EventRepositoryBucket $eventRepositoryBucket,
        LogRepositoryBucket $logRepositoryBucket
    ) {
        parent::__construct($repositoryBucket);

        $this->eventRepositoryBucket = $eventRepositoryBucket;
        $this->logRepositoryBucket = $logRepositoryBucket;
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('apisearch:create-index')
            ->setDescription('Create an index')
            ->addArgument(
                'repository',
                InputArgument::REQUIRED,
                'Repository'
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
                'with-events',
                null,
                InputOption::VALUE_NONE,
                'Create events as well'
            )
            ->addOption(
                'with-logs',
                null,
                InputOption::VALUE_NONE,
                'Create logs as well'
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
        $repository = $input->getArgument('repository');
        $index = $input->getArgument('index');

        try {
            $this
                ->repositoryBucket
                ->findRepository($repository, $index)
                ->createIndex(
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

        if ($input->getOption('with-events')) {
            $this->createEvents(
                $repository,
                $index,
                $output
            );
        }

        if ($input->getOption('with-logs')) {
            $this->createLogs(
                $repository,
                $index,
                $output
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

    /**
     * Create events index.
     *
     * @param string          $repositoryName
     * @param string          $index
     * @param OutputInterface $output
     */
    private function createEvents(
        string $repositoryName,
        string $index,
        OutputInterface $output
    ) {
        try {
            $this
                ->eventRepositoryBucket
                ->findRepository($repositoryName, $index)
                ->createIndex();
        } catch (ResourceNotAvailableException $exception) {
            $this->printInfoMessage(
                $output,
                $this->getHeader(),
                'Events index is already created. Skipping.'
            );
        }
    }

    /**
     * Create logs index.
     *
     * @param string          $repositoryName
     * @param string          $index
     * @param OutputInterface $output
     */
    private function createLogs(
        string $repositoryName,
        string $index,
        OutputInterface $output
    ) {
        try {
            $this
                ->logRepositoryBucket
                ->findRepository($repositoryName, $index)
                ->createIndex();
        } catch (ResourceNotAvailableException $exception) {
            $this->printInfoMessage(
                $output,
                $this->getHeader(),
                'Logs index is already created. Skipping.'
            );
        }
    }
}
