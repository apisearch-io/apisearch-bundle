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

namespace Apisearch\Tests\Functional\DependencyInjection\CompilerPass;

use Apisearch\ApisearchBundle;
use Apisearch\App\HttpAppRepository;
use Apisearch\Repository\HttpRepository;
use Apisearch\Repository\TransformableRepository;
use Apisearch\User\HttpUserRepository;
use Mmoreram\BaseBundle\Kernel\BaseKernel;
use Mmoreram\BaseBundle\Tests\BaseFunctionalTest;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class HttpRepositoryCompilerPassTest.
 */
class HttpRepositoryCompilerPassTest extends BaseFunctionalTest
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
                ApisearchBundle::class,
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
                            'test' => true,
                            'app_id' => '4444',
                            'endpoint' => 'xxx',
                            'token' => 'lala',
                            'indices' => [
                                'default' => 'xxx',
                            ],
                        ],
                        'another' => [
                            'adapter' => 'http',
                            'test' => true,
                            'app_id' => 12345,
                            'endpoint' => 'xxx',
                            'token' => 'lala',
                            'indices' => [
                                'default' => 67890,
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
     * Test repositories.
     */
    public function testRepositories()
    {
        $this->assertInstanceof(
            HttpAppRepository::class,
            $this->get('apisearch.app_repository_main')
        );

        $this->assertInstanceof(
            HttpUserRepository::class,
            $this->get('apisearch.user_repository_main')
        );

        $this->assertInstanceof(
            TransformableRepository::class,
            $this->get('apisearch.repository_main.default')
        );

        $this->assertInstanceof(
            TransformableRepository::class,
            $this->get('apisearch.repository_another.default')
        );

        $this->assertInstanceof(
            HttpRepository::class,
            $this->get('apisearch.repository_main.default')->getRepository()
        );

        $this->assertInstanceof(
            HttpRepository::class,
            $this->get('apisearch.repository_another.default')->getRepository()
        );
    }
}
