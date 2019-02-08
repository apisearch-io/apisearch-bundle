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

use Apisearch\Model\TokenUUID;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DeleteTokenCommand.
 */
class DeleteTokenCommand extends WithAppRepositoryBucketCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setDescription('Delete a token')
            ->addArgument(
                'app-name',
                InputArgument::REQUIRED,
                'App Name'
            )
            ->addArgument(
                'uuid',
                InputArgument::REQUIRED,
                'UUID'
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
        $this->getRepositoryAndIndices($input, $output);

        $this
            ->repositoryBucket->findRepository($input->getArgument('app-name'))
            ->deleteToken(
                TokenUUID::createById($input->getArgument('uuid'))
            );
    }

    /**
     * Dispatch domain event.
     *
     * @return string
     */
    protected static function getHeader(): string
    {
        return 'Delete token';
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
        return sprintf(
            'Token with UUID <%s> deleted properly',
            $input->getArgument('uuid')
        );
    }
}
