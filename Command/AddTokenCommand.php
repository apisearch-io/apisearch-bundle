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
 */

declare(strict_types=1);

namespace Apisearch\Command;

use Apisearch\Token\Token;
use Apisearch\Token\TokenUUID;
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
            ->setName('apisearch:add-token')
            ->setDescription('Add a token')
            ->addArgument(
                'repository',
                InputArgument::REQUIRED,
                'Repository name'
            )
            ->addArgument(
                'uuid',
                InputArgument::OPTIONAL,
                'Token UUID. If none defined, a new one will be generated',
                Uuid::uuid4()->toString()
            )
            ->addOption(
                'index',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Indices',
                []
            )
            ->addOption(
                'http-referrer',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Http referrers',
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
                'seconds-valid',
                null,
                InputOption::VALUE_OPTIONAL,
                'Seconds valid',
                Token::INFINITE_DURATION
            )
            ->addOption(
                'max-hits-per-query',
                null,
                InputOption::VALUE_OPTIONAL,
                'Maximum hits per query',
                Token::INFINITE_HITS_PER_QUERY
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
     * @return string
     */
    protected function getHeader(): string
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
    protected function getSuccessMessage(
        InputInterface $input,
        $result
    ): string {
        return sprintf(
            'Token with UUID <strong>%s</strong> added properly',
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
        list($app_id, $indices) = $this->getRepositoryAndIndices($input, $output);
        $endpoints = $this->getEndpoints($input, $output);

        $this
            ->repositoryBucket->findRepository($input->getArgument('repository'))
            ->addToken(
                new Token(
                    TokenUUID::createById($input->getArgument('uuid')),
                    (string) $app_id,
                    $indices,
                    $input->getOption('http-referrer'),
                    $endpoints,
                    $input->getOption('plugin'),
                    (int) $input->getOption('seconds-valid'),
                    (int) $input->getOption('max-hits-per-query'),
                    (int) $input->getOption('ttl')
                )
            );
    }
}
