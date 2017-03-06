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

namespace Puntmig\Search;

use Mmoreram\BaseBundle\SimpleBaseBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class PuntmigSearchBundle.
 */
class PuntmigSearchBundle extends SimpleBaseBundle
{
    /**
     * get config files.
     *
     * @return array
     */
    public function getConfigFiles() : array
    {
        return [
            'repositories',
            'query',
            'twig',
            'http',
        ];
    }

    /**
     * Return all bundle dependencies.
     *
     * Values can be a simple bundle namespace or its instance
     *
     * @return array
     */
    public static function getBundleDependencies(KernelInterface $kernel) : array
    {
        return [
            FrameworkBundle::class,
        ];
    }
}
