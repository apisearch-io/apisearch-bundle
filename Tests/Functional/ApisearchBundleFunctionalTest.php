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

namespace Apisearch\Tests\Functional;

use Apisearch\ApisearchBundle;
use Mmoreram\BaseBundle\Kernel\BaseKernel;
use Mmoreram\BaseBundle\Tests\BaseFunctionalTest;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class ApisearchBundleFunctionalTest.
 */
abstract class ApisearchBundleFunctionalTest extends BaseFunctionalTest
{
    /**
     * @var int
     *
     * Apisearch server port
     */
    const APISEARCH_SERVER_PORT = 8200;

    /**
     * @var string
     *
     * Token
     */
    const TOKEN = '0e4d75ba-c640-44c1-a745-06ee51db4e93';

    /**
     * Get kernel.
     *
     * @return KernelInterface
     */
    protected static function getKernel(): KernelInterface
    {
        return new BaseKernel(
            [
                ApisearchBundle::class,
            ], [
                'parameters' => [
                    'kernel.secret' => 'sdhjshjkds',
                ],
                'framework' => [
                    'test' => true,
                ],
                'apisearch' => [
                    'load_commands' => static::loadCommands(),
                    'repositories' => [
                        'main' => [
                            'adapter' => 'http',
                            'http' => [
                                'retry_map' => [
                                    'items' => [
                                        'url' => '/items',
                                        'method' => 'get',
                                        'retries' => 3,
                                        'microseconds_between_retries' => 1000,
                                    ],
                                    'items2' => [
                                        'url' => '/items2',
                                        'method' => 'get',
                                    ],
                                    'items3' => [
                                        'url' => '/items3',
                                    ],
                                    'default' => null,
                                ],
                            ],
                            'endpoint' => '~',
                            'app_id' => '~',
                            'token' => '~',
                            'test' => true,
                            'indices' => [
                                'default' => 'xxx',
                            ],
                        ],
                        'main2' => [
                            'adapter' => 'in_memory',
                            'endpoint' => '~',
                            'app_id' => '~',
                            'token' => '~',
                            'test' => true,
                            'indices' => [
                                'default' => 'xxx',
                            ],
                        ],
                        'main3' => [
                            'adapter' => 'service',
                            'test' => true,
                            'indices' => [
                                'default' => 'xxx',
                            ],
                        ],
                        'app123name' => [
                            'adapter' => 'http',
                            'endpoint' => 'localhost:'.self::APISEARCH_SERVER_PORT,
                            'app_id' => 'app123',
                            'token' => self::TOKEN,
                            'indices' => [
                                'index123name' => 'index123',
                            ],
                        ],
                    ],
                ],
            ],
            [],
            'prod',
            false
        );
    }

    /**
     * Load commands.
     *
     * @return bool
     */
    protected static function loadCommands(): bool
    {
        return true;
    }
}
