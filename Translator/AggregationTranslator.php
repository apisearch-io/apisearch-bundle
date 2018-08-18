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

namespace Apisearch\Translator;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class AggregationTranslator.
 */
class AggregationTranslator
{
    /**
     * @var TranslatorInterface
     *
     * Symfony Translator
     */
    private $symfonyTranslator;

    /**
     * Translator constructor.
     *
     * @param null|TranslatorInterface $symfonyTranslator
     */
    public function __construct(? TranslatorInterface $symfonyTranslator)
    {
        $this->symfonyTranslator = $symfonyTranslator;
    }

    /**
     * Translate title.
     *
     * @param string $title
     * @param string $prefix
     *
     * @return string
     */
    public function translateTitle(
        string $title,
        string $prefix = ''
    ): string {
        $key = ltrim("$prefix.aggregation.$title.label", '.');
        $trans = $this
            ->symfonyTranslator
            ->trans($key, [], 'aggregations');

        return $trans === $key
            ? $title
            : $trans;
    }

    /**
     * Translate title.
     *
     * @param string $title
     * @param string $option
     * @param string $prefix
     *
     * @return string
     */
    public function translateOption(
        string $title,
        string $option,
        string $prefix = ''
    ): string {
        $transformedOption = str_replace('.', '~', $option);
        $key = ltrim("$prefix.aggregation.$title.option.$transformedOption", '.');
        $trans = $this
            ->symfonyTranslator
            ->trans($key, [], 'aggregations');

        return $trans === $key
            ? $option
            : $trans;
    }
}
