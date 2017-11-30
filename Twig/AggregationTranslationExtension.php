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

namespace Apisearch\Twig;

use Apisearch\Translator\AggregationTranslator;
use Twig_Extension;
use Twig_SimpleFilter;

/**
 * Class AggregationTranslationExtension.
 */
class AggregationTranslationExtension extends Twig_Extension
{
    /**
     * @var AggregationTranslator
     *
     * Translator
     */
    private $translator;

    /**
     * AggregationTranslationExtension constructor.
     *
     * @param AggregationTranslator $translator
     */
    public function __construct(AggregationTranslator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return Twig_SimpleFilter[] An array of filters
     */
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('trans_title', [$this->translator, 'translateTitle']),
            new Twig_SimpleFilter('trans_option', [$this->translator, 'translateOption']),
        ];
    }
}
