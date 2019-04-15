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

use Apisearch\Http\Endpoints;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Class ApisearchCommand.
 */
abstract class ApisearchCommand extends Command
{
    /**
     * Start command.
     *
     * @param OutputInterface $output      Output
     * @param bool            $longCommand Show long time message
     *
     * @return Stopwatch
     */
    protected static function startCommand(
        OutputInterface $output,
        $longCommand = false
    ): Stopwatch {
        self::configureFormatter($output);
        $stopwatch = new Stopwatch();
        $stopwatch->start('command');
        $output->writeln('');
        self::printSystemMessage(
            $output,
            self::getProjectHeader(),
            'Command started at '.date('r')
        );

        if ($longCommand) {
            self::printMessage(
                $output,
                self::getProjectHeader(),
                'This process may take a few minutes. Please, be patient'
            );
        }
        $output->writeln('');

        return $stopwatch;
    }

    /**
     * Configure formatter with Apisearch specific style.
     *
     * @param OutputInterface $output
     */
    protected static function configureFormatter(OutputInterface $output)
    {
        $formatter = $output->getFormatter();
        $formatter->setStyle('system', new OutputFormatterStyle('green'));
        $formatter->setStyle('line', new OutputFormatterStyle('yellow'));
        $formatter->setStyle('fail', new OutputFormatterStyle('red'));
        $formatter->setStyle('info', new OutputFormatterStyle('blue'));
        $formatter->setStyle('body', new OutputFormatterStyle('white'));
        $formatter->setStyle('strong', new OutputFormatterStyle(null, null, ['bold']));
    }

    /**
     * Print system message.
     *
     * @param OutputInterface $output
     * @param string          $header
     * @param string          $body
     */
    protected static function printSystemMessage(
        OutputInterface $output,
        $header,
        $body
    ) {
        self::printStructureMessage(
            $output,
            $header,
            $body,
            'system'
        );
    }

    /**
     * Print line message.
     *
     * @param OutputInterface $output
     * @param string          $header
     * @param string          $body
     */
    protected static function printMessage(
        OutputInterface $output,
        $header,
        $body
    ) {
        self::printStructureMessage(
            $output,
            $header,
            $body,
            'line'
        );
    }

    /**
     * Print info message.
     *
     * @param OutputInterface $output
     * @param string          $header
     * @param string          $body
     */
    protected static function printInfoMessage(
        OutputInterface $output,
        $header,
        $body
    ) {
        self::printStructureMessage(
            $output,
            $header,
            $body,
            'info'
        );
    }

    /**
     * Print fail message.
     *
     * @param OutputInterface $output
     * @param string          $header
     * @param string          $body
     */
    protected static function printMessageFail(
        OutputInterface $output,
        $header,
        $body
    ) {
        self::printStructureMessage(
            $output,
            $header,
            $body,
            'fail'
        );
    }

    /**
     * Print message.
     *
     * @param OutputInterface $output
     * @param string          $header
     * @param string          $body
     * @param string          $type
     */
    private static function printStructureMessage(
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
     * @param OutputInterface $output
     */
    protected static function finishCommand(
        Stopwatch $stopwatch,
        OutputInterface $output
    ) {
        $output->writeln('');
        $event = $stopwatch->stop('command');

        self::printSystemMessage(
            $output,
            self::getProjectHeader(),
            'Command finished in '.$event->getDuration().' milliseconds'
        );

        self::printSystemMessage(
            $output,
            self::getProjectHeader(),
            'Max memory used: '.$event->getMemory().' bytes'
        );

        $output->writeln('');
    }

    /**
     * Get endpoint compositions given their names.
     *
     * @param InputInterface $input
     *
     * @return array
     *
     * @throws Exception
     */
    protected function getEndpoints(InputInterface $input): array
    {
        $endpointsName = $input->getOption('endpoint');
        $endpoints = Endpoints::filter($endpointsName);

        if (count($endpointsName) > count($endpoints)) {
            throw new Exception(sprintf(
                'Endpoint not found. Endpoints available: %s',
                implode(', ', array_keys(Endpoints::all()))
            ));
        }

        return $endpoints;
    }

    /**
     * Get project header.
     *
     * @return string Get project header
     */
    protected static function getProjectHeader()
    {
        return 'Apisearch';
    }
}
