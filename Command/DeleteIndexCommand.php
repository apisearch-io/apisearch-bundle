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

use Apisearch\Exception\ResourceNotAvailableException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DeleteIndexCommand.
 */
class DeleteIndexCommand extends WithAppRepositoryBucketCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'apisearch:delete-index';

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setDescription('Delete an index')
            ->addArgument(
                'app-name',
                InputArgument::REQUIRED,
                'App name'
            )
            ->addArgument(
                'index-name',
                InputArgument::REQUIRED,
                'Index name'
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
        list($_, $indexUUID) = $this->getRepositoryAndIndex($input, $output);

        try {
            $this
                ->repositoryBucket
                ->findRepository($appName)
                ->deleteIndex($indexUUID);
        } catch (ResourceNotAvailableException $exception) {
            self::printInfoMessage(
                $output,
                $this->getHeader(),
                'Index not found. Skipping.'
            );
        }
    }

    /**
     * Dispatch domain event.
     *
     * @return string
     */
    protected static function getHeader(): string
    {
        return 'Delete index';
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
        return 'Index deleted properly';
    }
}
