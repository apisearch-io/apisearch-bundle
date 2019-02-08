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
 * Class IndexCommandTest.
 */
class IndexCommandTest extends ApisearchBundleFunctionalTest
{
    /**
     * Test token creation.
     */
    public function testTokenCreation()
    {
        $fileName = tempnam('/tmp', 'test-apisearch');

        static::runCommand([
            'command' => 'apisearch:create-index',
            'app-name' => 'app123name',
            'index-name' => 'index123name',
        ]);

        $importOutput = static::runCommand([
            'command' => 'apisearch:import-index',
            'app-name' => 'app123name',
            'index-name' => 'index123name',
            'file' => __DIR__.'/data.as',
        ]);

        $this->assertTrue(
            false !== strpos($importOutput, 'Partial import of 28 items')
        );

        $exportOutput = static::runCommand([
            'command' => 'apisearch:export-index',
            'app-name' => 'app123name',
            'index-name' => 'index123name',
            'file' => $fileName,
        ]);

        $this->assertEquals(
            file_get_contents(__DIR__.'/data.as'),
            file_get_contents($fileName)
        );

        $this->assertTrue(
            false !== strpos($exportOutput, 'Partial export of 28 items')
        );

        static::runCommand([
            'command' => 'apisearch:delete-index',
            'app-name' => 'app123name',
            'index-name' => 'index123name',
        ]);

        unlink($fileName);
    }
}
