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
use Apisearch\App\InMemoryAppRepository;
use Apisearch\Repository\InMemoryRepository;
use Apisearch\Repository\TransformableRepository;
use Apisearch\User\InMemoryUserRepository;
use Mmoreram\BaseBundle\Kernel\BaseKernel;
use Mmoreram\BaseBundle\Tests\BaseFunctionalTest;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class InMemoryRepositoryCompilerPassTest.
 */
class InMemoryRepositoryCompilerPassTest extends BaseFunctionalTest
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
                            'adapter' => 'in_memory',
                            'test' => true,
                            'indexes' => [
                                'default' => 'xxx',
                            ],
                        ],
                        'another' => [
                            'adapter' => 'in_memory',
                            'test' => true,
                            'app_id' => 12345,
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
            InMemoryAppRepository::class,
            $this->get('apisearch.app_repository_main')
        );

        $this->assertInstanceof(
            InMemoryUserRepository::class,
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
            InMemoryRepository::class,
            $this->get('apisearch.repository_main.default')->getRepository()
        );

        $this->assertInstanceof(
            InMemoryRepository::class,
            $this->get('apisearch.repository_another.default')->getRepository()
        );
    }
}
