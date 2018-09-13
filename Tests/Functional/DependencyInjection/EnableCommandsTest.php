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

namespace Apisearch\Tests\Functional\DependencyInjection;

use Apisearch\ApisearchBundle;
use Mmoreram\BaseBundle\Kernel\BaseKernel;
use Mmoreram\BaseBundle\Tests\BaseFunctionalTest;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class EnableCommandsTest.
 */
class EnableCommandsTest extends BaseFunctionalTest
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
                    'load_commands' => true,
                    'repositories' => [
                        'main' => [
                            'adapter' => 'in_memory',
                            'app_id' => 'nnn',
                            'token' => 'lll',
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
     * Test that commands are not loaded.
     */
    public function testCommandsAreDisabled()
    {
        $content = static::runCommand([
            'command' => 'apisearch:create-index',
            'app-name' => 'main',
            'index' => 'default',
        ]);

        $this->assertFalse(
            strpos($content, 'There are no commands defined in the "apisearch" namespace')
        );
    }
}
