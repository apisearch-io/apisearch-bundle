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

use Apisearch\Config\Config;
use Apisearch\Config\Synonym;
use Apisearch\Exception\ResourceNotAvailableException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ConfigureIndexCommand.
 */
class ConfigureIndexCommand extends IndexBasedCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'apisearch:configure-index';

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
        $synonyms = $this->loadSynonyms($input);

        try {
            $this
                ->repositoryBucket
                ->findRepository($appName)
                ->configureIndex(
                    $indexUUID,
                    Config::createFromArray([
                        'language' => $input->getOption('language'),
                        'store_searchable_metadata' => !$input->getOption('no-store-searchable-metadata'),
                        'synonyms' => $synonyms = array_map(function (Synonym $synonym) {
                            return $synonym->toArray();
                        }, $synonyms),
                        'shards' => $input->getOption('shards'),
                        'replicas' => $input->getOption('replicas'),
                    ])
                );
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
        return 'Configure index';
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
        return 'Index configured properly';
    }
}
