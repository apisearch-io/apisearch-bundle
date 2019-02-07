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
use Apisearch\Tests\Functional\ProductBothTransformer;
use Apisearch\Tests\Functional\ProductReadTransformer;
use Apisearch\Tests\Functional\ProductWriteTransformer;
use Mmoreram\BaseBundle\Kernel\BaseKernel;
use Mmoreram\BaseBundle\Tests\BaseFunctionalTest;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class TransformersCompilerPassTest.
 */
class TransformersCompilerPassTest extends BaseFunctionalTest
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
                'services' => [
                    'read_transformer' => [
                        'class' => ProductReadTransformer::class,
                        'tags' => [
                            ['name' => 'apisearch.read_transformer'],
                        ],
                    ],
                    'write_transformer' => [
                        'class' => ProductWriteTransformer ::class,
                        'tags' => [
                            ['name' => 'apisearch.write_transformer'],
                        ],
                    ],
                    'both_transformer' => [
                        'class' => ProductBothTransformer::class,
                        'tags' => [
                            ['name' => 'apisearch.read_transformer'],
                            ['name' => 'apisearch.write_transformer'],
                        ],
                    ],
                ],
                'apisearch' => [
                    'repositories' => [
                        'main' => [
                            'adapter' => 'service',
                            'test' => true,
                            'indices' => [
                                'default' => 'xxx',
                            ],
                        ],
                        'another' => [
                            'adapter' => 'service',
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
     * Test read and write compiler pass.
     */
    public function testBothTransormers()
    {
        // No error means that the transformers have been properly built
    }
}
