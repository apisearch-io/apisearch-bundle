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

use Apisearch\Model\Item;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ImportIndexCommand.
 */
class ImportIndexCommand extends WithRepositoryBucketCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'apisearch:import-index';

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setDescription('Import your index')
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
                'file',
                InputArgument::REQUIRED,
                'File'
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
        $file = $input->getArgument('file');

        self::importFromFile(
            $file,
            $output,
            function (array $items, bool $lastIteration) use ($repository) {
                foreach ($items as $item) {
                    $repository->addItem($item);
                }

                $repository->flush(500, !$lastIteration);
            }
        );
    }

    /**
     * Import from file and return total number of elements.
     *
     * @param string          $file
     * @param OutputInterface $output
     * @param callable        $saveItems
     *
     * @return int
     */
    public static function importFromFile(
        string $file,
        OutputInterface $output,
        callable $saveItems
    ): int {
        $itemsBuffer = [];
        $itemsNb = 0;

        if (false !== ($handle = fopen($file, 'r'))) {
            while (false !== ($data = fgetcsv($handle, 0, ','))) {
                $itemAsArray = [
                    'uuid' => [
                        'id' => $data[0],
                        'type' => $data[1],
                    ],
                    'metadata' => json_decode($data[2], true),
                    'indexed_metadata' => json_decode($data[3], true),
                    'searchable_metadata' => json_decode($data[4], true),
                    'exact_matching_metadata' => json_decode($data[5], true),
                    'suggest' => json_decode($data[6], true),
                ];

                if (is_array($data[7])) {
                    $itemAsArray['coordinate'] = $data[7];
                }

                $item = Item::createFromArray($itemAsArray);
                $itemsBuffer[] = $item;
                ++$itemsNb;

                if (count($itemsBuffer) >= 500) {
                    $saveItems($itemsBuffer, false);
                    self::printPartialCountSaved($output, count($itemsBuffer));

                    $itemsBuffer = [];
                }
            }

            $saveItems($itemsBuffer, true);
            self::printPartialCountSaved($output, count($itemsBuffer));
        }

        fclose($handle);

        return $itemsNb;
    }

    /**
     * Print partial save.
     *
     * @param OutputInterface $output
     * @param int             $count
     */
    private static function printPartialCountSaved(
        OutputInterface $output,
        int $count
    ) {
        self::printInfoMessage(
            $output,
            self::getHeader(),
            sprintf('Partial import of %d items', $count)
        );
    }

    /**
     * Dispatch domain event.
     *
     * @return string
     */
    protected static function getHeader(): string
    {
        return 'Import index';
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
        return 'Index imported properly';
    }
}
