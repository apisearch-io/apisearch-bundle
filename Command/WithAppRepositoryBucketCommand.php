<?php

/*
 * This file is part of the Search PHP Bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 * @author PuntMig Technologies
 */

declare(strict_types=1);

namespace Apisearch\Command;

use Apisearch\App\AppRepositoryBucket;

/**
 * Class WithAppRepositoryBucketCommand.
 */
abstract class WithAppRepositoryBucketCommand extends ApisearchFormattedCommand
{
    /**
     * @var AppRepositoryBucket
     *
     * Repository bucket
     */
    protected $repositoryBucket;

    /**
     * WithAppRepositoryBucketCommand constructor.
     *
     * @param AppRepositoryBucket $repositoryBucket
     */
    public function __construct(AppRepositoryBucket $repositoryBucket)
    {
        parent::__construct();

        $this->repositoryBucket = $repositoryBucket;
    }
}
