<?php

declare(strict_types=1);

/*
 * This file is part of the smnandre/packapi package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PackApi\Provider;

use PackApi\Model\ActivitySummary;
use PackApi\Package\Package;

interface ActivityProviderInterface
{
    public function supports(Package $package): bool;

    public function getActivitySummary(Package $package): ?ActivitySummary;
}
