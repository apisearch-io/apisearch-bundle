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

use Apisearch\App\AppRepository;
use Apisearch\Repository\Repository;
use Apisearch\Repository\TransformableRepository;
use Apisearch\User\UserRepository;

/**
 * Class AService.
 */
class AService
{
    public $appRepo1;
    public $appRepo2;
    public $appRepo3;

    public $userRepo1;
    public $userRepo2;

    public $repo1;
    public $repo2;

    /**
     * AService constructor.
     *
     * @param AppRepository           $apisearchMain2AppRepository
     * @param AppRepository           $apisearchApp123nameAppRepository
     * @param AppRepository           $apisearchMainAppRepository
     * @param UserRepository          $apisearchMainUserRepository
     * @param UserRepository          $apisearchApp123nameUserRepository
     * @param Repository              $apisearchMain2DefaultRepository
     * @param TransformableRepository $apisearchApp123nameIndex123nameRepository
     */
    public function __construct(
        AppRepository $apisearchMain2AppRepository,
        AppRepository $apisearchApp123nameAppRepository,
        AppRepository $apisearchMainAppRepository,

        UserRepository $apisearchMainUserRepository,
        UserRepository $apisearchApp123nameUserRepository,

        Repository $apisearchMain2DefaultRepository,
        TransformableRepository $apisearchApp123nameIndex123nameRepository
    ) {
        $this->appRepo1 = $apisearchMain2AppRepository;
        $this->appRepo2 = $apisearchApp123nameAppRepository;
        $this->appRepo3 = $apisearchMainAppRepository;

        $this->userRepo1 = $apisearchMainUserRepository;
        $this->userRepo2 = $apisearchApp123nameUserRepository;

        $this->repo1 = $apisearchMain2DefaultRepository;
        $this->repo2 = $apisearchApp123nameIndex123nameRepository;
    }
}
