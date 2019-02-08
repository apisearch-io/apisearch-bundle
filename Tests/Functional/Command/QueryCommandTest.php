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

namespace Apisearch\Tests\Functional\Command;

use Apisearch\Tests\Functional\ApisearchBundleFunctionalTest;

/**
 * Class QueryCommandTest.
 */
class QueryCommandTest extends ApisearchBundleFunctionalTest
{
    /**
     * Test query.
     */
    public function testQuery()
    {
        static::runCommand([
            'command' => 'apisearch:create-index',
            'app-name' => 'app123name',
            'index-name' => 'index123name',
        ]);

        static::runCommand([
            'command' => 'apisearch:import-index',
            'app-name' => 'app123name',
            'index-name' => 'index123name',
            'file' => __DIR__.'/data.as',
        ]);

        $queryOutput = static::runCommand([
            'command' => 'apisearch:query',
            'app-name' => 'app123name',
            'index-name' => 'index123name',
        ]);

        $this->assertTrue(
            false !== strpos($queryOutput, '[Number of hits] 10')
        );

        $this->assertTrue(
            false !== strpos($queryOutput, '[Number of resources in index] 28')
        );
    }
}
