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
 * Class AddDeleteTokensCommandTest.
 */
class AddDeleteTokensCommandTest extends ApisearchBundleFunctionalTest
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

        static::runCommand([
            'command' => 'apisearch:add-token',
            'uuid' => 'new-token-999',
            'app-name' => 'app123name',
        ]);

        $output = static::runCommand([
            'command' => 'apisearch:print-tokens',
            'app-name' => 'app123name',
        ]);

        $this->assertTrue(
            strpos($output, 'new-token-999') > 0
        );

        static::runCommand([
            'command' => 'apisearch:delete-token',
            'uuid' => 'new-token-999',
            'app-name' => 'app123name',
        ]);

        $output = static::runCommand([
            'command' => 'apisearch:print-tokens',
            'app-name' => 'app123name',
        ]);

        $this->assertFalse(
            strpos($output, 'new-token-999') > 0
        );
    }
}
