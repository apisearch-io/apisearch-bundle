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

namespace Puntmig\Search\Tests\Functional;

use Mmoreram\BaseBundle\Kernel\BaseKernel;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

use Puntmig\Search\PuntmigSearchBundle;

/**
 * Class ApiKeyParameterExceptionTest.
 */
class ApiKeyParameterExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test fail.
     */
    public function testApiKeyException()
    {
        $kernel = new BaseKernel([
            PuntmigSearchBundle::class,
        ]);

        try {
            $kernel->boot();
            $this->fail('Api key should be required. Kernel booted without value for puntmig_search.api_key');
        } catch (InvalidConfigurationException $exception) {
            // Silent pass
        }
    }
}
