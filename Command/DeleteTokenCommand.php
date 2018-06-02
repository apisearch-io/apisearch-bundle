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

use Apisearch\Token\TokenUUID;
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
            ->setName('apisearch:delete-token')
            ->setDescription('Delete a token')
            ->addArgument(
                'repository',
                InputArgument::REQUIRED,
                'Repository Name'
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
     * @return string
     */
    protected function getHeader(): string
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
    protected function getSuccessMessage(
        InputInterface $input,
        $result
    ): string {
        return sprintf(
            'Token with UUID <%s> deleted properly',
            $input->getArgument('uuid')
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
            ->repositoryBucket->findRepository($input->getArgument('repository'))
            ->deleteToken(
                TokenUUID::createById($input->getArgument('uuid'))
            );
    }
}
