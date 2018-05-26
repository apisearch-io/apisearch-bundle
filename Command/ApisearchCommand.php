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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

/**
 * Class ApisearchCommand.
 */
abstract class ApisearchCommand extends Command
{
    /**
     * @var Stopwatch
     *
     * Stopwatch instance
     */
    private $stopwatch;

    /**
     * Start command.
     *
     * @param OutputInterface $output      Output
     * @param bool            $longCommand Show long time message
     *
     * @return $this Self object
     */
    protected function startCommand(
        OutputInterface $output,
        $longCommand = false
    ) {
        $this->configureFormatter($output);
        $this->stopwatch = new Stopwatch();
        $this->startStopWatch('command');
        $output->writeln('');
        $this
            ->printSystemMessage(
                $output,
                $this->getProjectHeader(),
                'Command started at '.date('r')
            );
        if ($longCommand) {
            $this
                ->printMessage(
                    $output,
                    $this->getProjectHeader(),
                    'This process may take a few minutes. Please, be patient'
                );
        }
        $output->writeln('');

        return $this;
    }

    /**
     * Configure formatter with Elcodi specific style.
     *
     * @param OutputInterface $output Output
     *
     * @return $this Self object
     */
    protected function configureFormatter(OutputInterface $output)
    {
        $formatter = $output->getFormatter();
        $formatter->setStyle('system', new OutputFormatterStyle('green'));
        $formatter->setStyle('line', new OutputFormatterStyle('yellow'));
        $formatter->setStyle('failLine', new OutputFormatterStyle('red'));
        $formatter->setStyle('info', new OutputFormatterStyle('blue'));
        $formatter->setStyle('body', new OutputFormatterStyle('white'));

        return $this;
    }

    /**
     * Start stopwatch.
     *
     * @param string $eventName Event name
     *
     * @return StopwatchEvent Event
     */
    protected function startStopWatch($eventName)
    {
        return $this
            ->stopwatch
            ->start($eventName);
    }

    /**
     * Print message.
     *
     * @param OutputInterface $output Output
     * @param string          $header Message header
     * @param string          $body   Message body
     *
     * @return ApisearchCommand
     */
    protected function printSystemMessage(
        OutputInterface $output,
        $header,
        $body
    ): ApisearchCommand {
        $this->printStructureMessage(
            $output,
            $header,
            $body,
            'system'
        );

        return $this;
    }

    /**
     * Print message.
     *
     * @param OutputInterface $output Output
     * @param string          $header Message header
     * @param string          $body   Message body
     *
     * @return ApisearchCommand
     */
    protected function printMessage(
        OutputInterface $output,
        $header,
        $body
    ) {
        $this->printStructureMessage(
            $output,
            $header,
            $body,
            'line'
        );

        return $this;
    }

    /**
     * Print message.
     *
     * @param OutputInterface $output Output
     * @param string          $header Message header
     * @param string          $body   Message body
     *
     * @return ApisearchCommand
     */
    protected function printInfoMessage(
        OutputInterface $output,
        $header,
        $body
    ): ApisearchCommand {
        $this->printStructureMessage(
            $output,
            $header,
            $body,
            'info'
        );

        return $this;
    }

    /**
     * Print message.
     *
     * @param OutputInterface $output Output
     * @param string          $header Message header
     * @param string          $body   Message body
     *
     * @return ApisearchCommand
     */
    protected function printMessageFail(
        OutputInterface $output,
        $header,
        $body
    ): ApisearchCommand {
        $this->printStructureMessage(
            $output,
            $header,
            $body,
            'failLine'
        );

        return $this;
    }

    /**
     * Print message.
     *
     * @param OutputInterface $output
     * @param string          $header
     * @param string          $body
     * @param string          $type
     */
    private function printStructureMessage(
        OutputInterface $output,
        $header,
        $body,
        $type
    ) {
        $message = sprintf(
            "<$type>%s</$type> <body>%s</body>",
            '['.$header.']',
            $body
        );
        $output->writeln($message);
    }

    /**
     * Finish command.
     *
     * @param OutputInterface $output Output
     *
     * @return $this Self object
     */
    protected function finishCommand(OutputInterface $output)
    {
        $output->writeln('');
        $event = $this->stopStopWatch('command');
        $this
            ->printSystemMessage(
                $output,
                $this->getProjectHeader(),
                'Command finished in '.$event->getDuration().' milliseconds'
            )
            ->printSystemMessage(
                $output,
                $this->getProjectHeader(),
                'Max memory used: '.$event->getMemory().' bytes'
            );
        $output->writeln('');

        return $this;
    }

    /**
     * Stop stopwatch.
     *
     * @param string $eventName Event name
     *
     * @return StopwatchEvent Event
     */
    protected function stopStopWatch($eventName)
    {
        return $this
            ->stopwatch
            ->stop($eventName);
    }

    /**
     * Get project header.
     *
     * @return string Get project header
     */
    protected function getProjectHeader()
    {
        return 'Apisearch';
    }
}
