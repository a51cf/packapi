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

use PackApi\Model\DownloadPeriod;
use PackApi\Model\DownloadStats;
use PackApi\Package\Package;

interface DownloadStatsProviderInterface
{
    /**
     * Whether this provider supports the given package type.
     */
    public function supports(Package $package): bool;

    /**
     * Retrieve download statistics for the package (default period, e.g. monthly).
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

    /**
     * List available periods (e.g. total, monthly, weekly) for this package.
     *
     * @return string[] List of period keys (e.g. ['total', 'monthly', 'weekly'])
     */
    public function getAvailablePeriods(Package $package): array;

    /**
     * Whether CDN stats (requests, bandwidth) are available for this package.
     */
    public function hasCdnStats(Package $package): bool;
}
