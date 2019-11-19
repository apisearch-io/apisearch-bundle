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

use Apisearch\App\AppRepositoryBucket;
use Apisearch\Config\Config;
use Apisearch\Config\Synonym;
use Apisearch\Config\SynonymReader;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class IndexBasedCommand.
 */
abstract class IndexBasedCommand extends WithAppRepositoryBucketCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setDescription('Configure an index')
            ->addArgument(
                'app-name',
                InputArgument::REQUIRED,
                'App name'
            )
            ->addArgument(
                'index-name',
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
            )
            ->addOption(
                'synonyms-file',
                null,
                InputOption::VALUE_OPTIONAL,
                'Synonyms file'
            )
            ->addOption(
                'shards',
                null,
                InputOption::VALUE_OPTIONAL,
                'Shards for the index',
                Config::DEFAULT_SHARDS
            )
            ->addOption(
                'replicas',
                null,
                InputOption::VALUE_OPTIONAL,
                'Replicas for the index',
                Config::DEFAULT_REPLICAS
            );
    }

    /**
     * @var SynonymReader
     *
     * Synonym Reader
     */
    protected $synonymReader;

    /**
     * WithAppRepositoryBucketCommand constructor.
     *
     * @param AppRepositoryBucket $repositoryBucket
     * @param SynonymReader       $synonymReader
     */
    public function __construct(
        AppRepositoryBucket $repositoryBucket,
        SynonymReader $synonymReader
    ) {
        parent::__construct($repositoryBucket);

        $this->synonymReader = $synonymReader;
    }

    /**
     * Load synonyms.
     *
     * @param InputInterface $input
     *
     * @return Synonym[]|array
     */
    protected function loadSynonyms(InputInterface $input): array
    {
        $synonymsFile = $input->getOption('synonyms-file');

        $synonyms = !is_null($synonymsFile)
            ? $this
                ->synonymReader
                ->readSynonymsFromFile($input->getOption('synonyms-file'))
            : [];

        $synonyms += $this
            ->synonymReader
            ->readSynonymsFromCommaSeparatedArray($input->getOption('synonym'));

        return $synonyms;
    }
}
