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

namespace Apisearch\Tests\Functional;

use Apisearch\ApisearchBundle;
use Apisearch\Exception\MockException;
use Apisearch\Http\Retry;
use Mmoreram\BaseBundle\BaseBundle;
use Mmoreram\BaseBundle\Kernel\BaseKernel;
use Mmoreram\BaseBundle\Tests\BaseFunctionalTest;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class ApisearchConfigurationTest.
 */
class ApisearchConfigurationTest extends BaseFunctionalTest
{
    /**
     * Get kernel.
     *
     * @return KernelInterface
     */
    protected static function getKernel(): KernelInterface
    {
        return new BaseKernel(
            [
                BaseBundle::class,
                ApisearchBundle::class,
                FrameworkBundle::class,
            ], [
                'parameters' => [
                    'kernel.secret' => 'sdhjshjkds',
                ],
                'framework' => [
                    'test' => true,
                ],
                'apisearch' => [
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
                            'indexes' => [
                                'default' => 'xxx',
                            ],
                        ],
                        'main2' => [
                            'adapter' => 'in_memory',
                            'endpoint' => '~',
                            'app_id' => '~',
                            'token' => '~',
                            'test' => true,
                            'indexes' => [
                                'default' => 'xxx',
                            ],
                        ],
                        'main3' => [
                            'adapter' => 'service',
                            'test' => true,
                            'indexes' => [
                                'default' => 'xxx',
                            ],
                        ],
                    ],
                ],
            ],
            [],
            'test',
            true
        );
    }

    /**
     * Test main repository configuration.
     */
    public function testMainRepositoryConfiguration()
    {
        $repositoriesConfiguration = self::getParameter('apisearch.repository_configuration');
        $this->assertEquals(
            'http',
            $repositoriesConfiguration['main']['adapter']
        );
        $this->assertEquals(
            [
                'items' => [
                    'url' => '/items',
                    'method' => 'get',
                    'retries' => 3,
                    'microseconds_between_retries' => 1000,
                ],
                'items2' => [
                    'url' => '/items2',
                    'method' => 'get',
                    'retries' => 0,
                    'microseconds_between_retries' => Retry::DEFAULT_MICROSECONDS_BETWEEN_RETRIES,
                ],
                'items3' => [
                    'url' => '/items3',
                    'method' => '*',
                    'retries' => 0,
                    'microseconds_between_retries' => Retry::DEFAULT_MICROSECONDS_BETWEEN_RETRIES,
                ],
                'default' => [
                    'url' => '*',
                    'method' => '*',
                    'retries' => 0,
                    'microseconds_between_retries' => Retry::DEFAULT_MICROSECONDS_BETWEEN_RETRIES,
                ],
            ],
            $repositoriesConfiguration['main']['http']['retry_map']
        );
        static::get('apisearch.repository_main.default');
    }

    /**
     * Test main2 repository configuration.
     */
    public function testMain2()
    {
        $repositoriesConfiguration = self::getParameter('apisearch.repository_configuration');
        $this->assertEquals(
            'in_memory',
            $repositoriesConfiguration['main2']['adapter']
        );
        static::get('apisearch.repository_main2.default');
    }

    /**
     * Test main3 repository configuration.
     */
    public function testMain3()
    {
        $repositoriesConfiguration = self::getParameter('apisearch.repository_configuration');
        $this->assertEquals(
            'service',
            $repositoriesConfiguration['main3']['adapter']
        );

        $repository = static::get('apisearch.repository_main3.default');
        try {
            $repository->flush();
            $this->fail('A mock exception should be thrown here.');
        } catch (MockException $e) {
            // Silent pass
        }
    }
}
