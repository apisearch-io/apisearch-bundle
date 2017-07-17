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

/**
 * Class MethodAccessorExtension.
 */
class MethodAccessorExtension extends Twig_Extension
{
    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return Twig_SimpleFilter[] An array of filters
     */
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('call', function ($object, $method) {
                return $object->$method();
            }),
        ];
    }
}
