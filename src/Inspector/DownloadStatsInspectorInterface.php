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

namespace PackApi\Inspector;

use PackApi\Model\DownloadPeriod;
use PackApi\Model\DownloadStats;
use PackApi\Package\Package;

interface DownloadStatsInspectorInterface
{
    /**
     * Get download statistics for the given package from the first supporting provider.
     *
     * @return DownloadStats|null returns null if not supported or no data available
     */
    public function getStats(Package $package): ?DownloadStats;

    /**
     * Retrieve download statistics for a custom period.
     *
     * @return DownloadStats|null returns null if not supported or no data available
     */
    public function getStatsForPeriod(Package $package, DownloadPeriod $period): ?DownloadStats;
}
