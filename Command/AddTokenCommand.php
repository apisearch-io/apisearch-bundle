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

use Apisearch\Model\Token;
use Apisearch\Model\TokenUUID;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AddTokenCommand.
 */
class AddTokenCommand extends WithAppRepositoryBucketCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setDescription('Add a token')
            ->addArgument(
                'app-name',
                InputArgument::REQUIRED,
                'App name'
            )
            ->addArgument(
                'uuid',
                InputArgument::OPTIONAL,
                'Token UUID. If none defined, a new one will be generated',
                Uuid::uuid4()->toString()
            )
            ->addOption(
                'index-name',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Indices',
                []
            )
            ->addOption(
                'endpoint',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Endpoints',
                []
            )
            ->addOption(
                'plugin',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Plugins',
                []
            )
            ->addOption(
                'ttl',
                null,
                InputOption::VALUE_OPTIONAL,
                'TTL',
                Token::DEFAULT_TTL
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
        list($appUUID, $indices) = $this->getRepositoryAndIndices($input, $output);
        $endpoints = $this->getEndpoints($input);

        $this
            ->repositoryBucket->findRepository($input->getArgument('app-name'))
            ->addToken(
                new Token(
                    TokenUUID::createById($input->getArgument('uuid')),
                    $appUUID,
                    $indices,
                    $endpoints,
                    $input->getOption('plugin'),
                    (int) $input->getOption('ttl')
                )
            );
    }

    /**
     * Dispatch domain event.
     *
     * @return string
     */
    protected static function getHeader(): string
    {
        return 'Add token';
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
        return 'Token added properly';
    }
}
