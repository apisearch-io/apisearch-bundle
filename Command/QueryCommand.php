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
use Apisearch\Query\Query;
use Apisearch\Query\Query as ModelQuery;
use Apisearch\Result\Result;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class QueryCommand.
 */
class QueryCommand extends WithRepositoryBucketCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setDescription('Make a query')
            ->addArgument(
                'app-name',
                InputArgument::REQUIRED,
                'App name'
            )
            ->addArgument(
                'index-name',
                InputArgument::REQUIRED,
                'Index name'
            )
            ->addArgument(
                'query',
                InputArgument::OPTIONAL,
                'Query text',
                ''
            )
            ->addOption(
                'page',
                null,
                InputOption::VALUE_OPTIONAL,
                'Page',
                ModelQuery::DEFAULT_PAGE
            )
            ->addOption(
                'size',
                null,
                InputOption::VALUE_OPTIONAL,
                'Number of results',
                ModelQuery::DEFAULT_SIZE
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
        $repository = $this->getRepository($input, $output);
        self::makeQueryAndPrintResults(
            $input,
            $output,
            function (Query $query) use ($repository) {
                return $repository->query($query);
            }
        );
    }

    /**
     * Make query and print results.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param callable        $makeQuery
     */
    public static function makeQueryAndPrintResults(
        InputInterface $input,
        OutputInterface $output,
        callable $makeQuery
    ) {
        $query = $input->getArgument('query');

        self::printInfoMessage(
            $output,
            'Query / Page / Size',
            sprintf('<strong>%s</strong> / %d / %d',
                '' === $query
                    ? '*'
                    : $query,
                $input->getOption('page'),
                $input->getOption('size')
            )
        );

        try {
            $result = $makeQuery(Query::create(
                $input->getArgument('query'),
                (int) $input->getOption('page'),
                (int) $input->getOption('size')
            ));

            self::printResult($output, $result);
        } catch (ResourceNotAvailableException $exception) {
            self::printInfoMessage(
                $output,
                self::getHeader(),
                $output->writeln('Index not found. Skipping.')
            );
        }
    }

    /**
     * Print results.
     *
     * @param OutputInterface $output
     * @param Result          $result
     */
    public static function printResult(
        OutputInterface $output,
        Result $result
    ) {
        self::printInfoMessage(
            $output,
            'Number of resources in index',
            $result->getTotalItems()
        );

        self::printInfoMessage(
            $output,
            'Number of hits',
            $result->getTotalHits()
        );

        $output->writeln('');

        foreach ($result->getItems() as $item) {
            $firstStringPosition = array_reduce($item->getAllMetadata(), function ($carry, $element) {
                return is_string($carry)
                    ? $carry
                    : (
                    is_string($element)
                        ? $element
                        : null
                    );
            }, null);

            self::printInfoMessage(
                $output,
                'x',
                sprintf('%s - %s',
                    $item->getUUID()->composeUUID(),
                    substr((string) $firstStringPosition, 0, 50)
                )
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
        return 'Query index';
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
