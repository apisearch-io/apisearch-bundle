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

use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ApisearchFormattedCommand.
 */
abstract class ApisearchFormattedCommand extends ApisearchCommand
{
    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @return null|int null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->startCommand($output);
        try {
            $result = $this->runCommand(
                $input,
                $output
            );

            $successfulMessage = $this->getSuccessMessage($input, $result);
            if (!empty($successfulMessage)) {
                $this->printMessage(
                    $output,
                    $this->getHeader(),
                    $this->getSuccessMessage($input, $result)
                );
            }
        } catch (Exception $e) {
            $this->printMessageFail(
                $output,
                $this->getHeader(),
                $e->getMessage()
            );
        }

        $this->finishCommand($output);
    }

    /**
     * Dispatch domain event.
     *
     * @return string
     */
    abstract protected function getHeader(): string;

    /**
     * Dispatch domain event.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return mixed|null
     */
    abstract protected function runCommand(InputInterface $input, OutputInterface $output);

    /**
     * Get success message.
     *
     * @param InputInterface $input
     * @param mixed          $result
     *
     * @return string
     */
    abstract protected function getSuccessMessage(
        InputInterface $input,
        $result
    ): string;
}
