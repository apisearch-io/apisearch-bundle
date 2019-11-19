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

/**
 * Class TestAutowiring.
 */
class AutowiringTest extends ApisearchBundleFunctionalTest
{
    /**
     * Test autowiring.
     */
    public function testAutowiring()
    {
        $service = $this->get(AService::class);
        $this->assertEquals('app2', $service->appRepo1->getAppUUID()->getId());
        $this->assertEquals('app123', $service->appRepo2->getAppUUID()->getId());
        $this->assertEquals('app1', $service->appRepo3->getAppUUID()->getId());

        $this->assertEquals('app1', $service->userRepo1->getAppUUID()->getId());
        $this->assertEquals('app123', $service->userRepo2->getAppUUID()->getId());

        $this->assertEquals('app2', $service->repo1->getAppUUID()->getId());
        $this->assertEquals('xxx', $service->repo1->getIndexUUID()->getId());
        $this->assertEquals('app123', $service->repo2->getAppUUID()->getId());
        $this->assertEquals('index123', $service->repo2->getIndexUUID()->getId());
    }
}
