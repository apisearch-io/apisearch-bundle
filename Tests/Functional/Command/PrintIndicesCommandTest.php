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
 * Class PrintIndicesCommandTest.
 */
class PrintIndicesCommandTest extends ApisearchBundleFunctionalTest
{
    /**
     * Test token creation.
     */
    public function testTokenCreation()
    {
        static::runCommand([
            'command' => 'apisearch:create-index',
            'app-name' => 'app123name',
            'index-name' => 'index123name',
        ]);

        $output = static::runCommand([
            'command' => 'apisearch:print-indices',
            'app-name' => 'app123name',
        ]);

        $this->assertTrue(
            strpos($output, 'index123 ') > 0
        );

        $output = static::runCommand([
            'command' => 'apisearch:print-indices',
            'app-name' => 'app123name',
            '--with-metadata' => true,
            '--with-fields' => true,
        ]);

        $this->assertTrue(
            strpos($output, 'Fields ') > 0
        );
    }
}
