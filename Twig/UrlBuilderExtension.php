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

namespace Puntmig\Search\Twig;

use Twig_Extension;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

use Puntmig\Search\Url\UrlBuilder;

/**
 * Class UrlBuilderExtension.
 */
class UrlBuilderExtension extends Twig_Extension
{
    /**
     * @var UrlBuilder
     *
     * Url builder
     */
    private $urlBuilder;

    /**
     * QueryExtension constructor.
     *
     * @param UrlBuilder $urlBuilder
     */
    public function __construct(UrlBuilder $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return Twig_SimpleFilter[] An array of filters
     */
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('add_filter_value', [$this->urlBuilder, 'addFilterValue']),
            new Twig_SimpleFilter('remove_filter_value', [$this->urlBuilder, 'removeFilterValue']),
            new Twig_SimpleFilter('remove_price_range_filter', [$this->urlBuilder, 'removePriceRangeFilter']),
            new Twig_SimpleFilter('add_prev_page', [$this->urlBuilder, 'addPrevPage']),
            new Twig_SimpleFilter('add_page', [$this->urlBuilder, 'addPage']),
            new Twig_SimpleFilter('add_next_page', [$this->urlBuilder, 'addNextPage']),
            new Twig_SimpleFilter('add_sort_by', [$this->urlBuilder, 'addSortBy']),
        ];
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return Twig_SimpleFunction[] An array of filters
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('set_routes_dictionary', [$this->urlBuilder, 'setRoutesDictionary']),
        ];
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     *
     * @deprecated since 1.26 (to be removed in 2.0), not used anymore internally
     */
    public function getName()
    {
        return 'url_builder_extension';
    }
}
