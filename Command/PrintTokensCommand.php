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

use Apisearch\Model\IndexUUID;
use Apisearch\Model\Token;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PrintTokensCommand.
 */
class PrintTokensCommand extends WithAppRepositoryBucketCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'apisearch:print-tokens';

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setDescription('Print all tokens of an app-id')
            ->addArgument(
                'app-name',
                InputArgument::REQUIRED,
                'App name'
            )
            ->addOption(
                'with-metadata',
                null,
                InputOption::VALUE_NONE,
                'Print metadata'
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
        $tokens = $this
            ->repositoryBucket
            ->findRepository($appName)
            ->getTokens();

        $indexArray = $this
                ->repositoryBucket
                ->getConfiguration()[$appName]['indices'] ?? [];

        /*
         * @var Token[]
         */
        foreach ($tokens as $token) {
            $indicesReversed = array_flip($indexArray);
            $indices = array_map(function (string $index) use ($indicesReversed) {
                return $indicesReversed[$index] ?? null;
            }, $token->getIndices());
            $indices = array_filter($indices);

            $token->setIndices(array_map(function (string $index) {
                return IndexUUID::createById($index);
            }, $indices));
        }

        /**
         * @var Token
         */
        static::printTokens(
            $input,
            $output,
            $tokens
        );
    }

    /**
     * Print tokens.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param Token[]         $tokens
     */
    public static function printTokens(
        InputInterface $input,
        OutputInterface $output,
        array $tokens
    ) {
        $withMetadata = $input->getOption('with-metadata');
        $table = new Table($output);
        $headers = ['UUID', 'Indices', 'endpoints', 'plugins', 'ttl'];
        if ($withMetadata) {
            $headers[] = 'Metadata';
        }
        $table->setHeaders($headers);

        foreach ($tokens as $token) {
            $row = [
                $token->getTokenUUID()->composeUUID(),
                implode(', ', array_map(function (IndexUUID $index) {
                    return $index->composeUUID();
                }, $token->getIndices())),
                implode(', ', $token->getEndpoints()),
                implode(', ', $token->getPlugins()),
                $token->getTtl(),
            ];

            if ($withMetadata) {
                $metadataRow = [];
                foreach ($token->getMetadata() as $metadataField => $metadataValue) {
                    $metadataRow[] = sprintf("$metadataField = %s",
                        is_bool($metadataValue)
                            ? $metadataValue ? 'true' : 'false'
                            : (
                                is_array($metadataValue)
                                    ? implode(', ', $metadataValue)
                                    : (string) $metadataValue
                            )
                    );
                }
                $row[] = implode(PHP_EOL, $metadataRow);
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
        return 'Print tokens';
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
