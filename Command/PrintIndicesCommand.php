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

use Apisearch\Model\Index;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PrintIndicesCommand.
 */
class PrintIndicesCommand extends WithAppRepositoryBucketCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('apisearch:print-indices')
            ->setDescription('Print all indices')
            ->addArgument(
                'app-name',
                InputArgument::REQUIRED,
                'App name'
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
        $indices = $this
            ->repositoryBucket
            ->findRepository($appName)
            ->getIndices();

        $table = new Table($output);
        $table->setHeaders(['UUID', 'App ID', 'Doc Count', 'Size', 'Ok?', 'shards', 'replicas']);
        /**
         * @var Index
         */
        foreach ($indices as $index) {
            $table->addRow([
                $index->getUUID()->composeUUID(),
                $index->getAppUUID()->composeUUID(),
                $index->getDocCount(),
                $index->getSize(),
                $index->isOK()
                    ? 'Yes'
                    : 'No',
                $index->getShards(),
                $index->getReplicas(),
            ]);
        }
        $table->render();
    }

    /**
     * Dispatch domain event.
     *
     * @return string
     */
    protected function getHeader(): string
    {
        return 'Print indices';
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
        return '';
    }
}
