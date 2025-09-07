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

namespace PackApi\Bridge\JsDelivr;

use PackApi\Model\DownloadPeriod;
use PackApi\Model\DownloadStats;
use PackApi\Package\Package;
use PackApi\Provider\DownloadStatsProviderInterface;

final class JsDelivrStatsProvider implements DownloadStatsProviderInterface
{
    public function __construct(private JsDelivrApiClient $client)
    {
    }

    public function supports(Package $package): bool
    {
        $id = $package->getIdentifier();

        return str_starts_with($id, 'npm/') || str_starts_with($id, 'composer/');
    }

    public function getStats(Package $package): ?DownloadStats
    {
        $id = $package->getIdentifier();
        $meta = $this->client->fetchPackageMeta($id);
        if (!$meta || empty($meta['hits'])) {
            return null;
        }
        $end = new \DateTimeImmutable();
        $start = $end->modify('-1 month');
        $period = new DownloadPeriod('monthly', (int) $meta['hits'], $start, $end);

        return new DownloadStats(['monthly' => $period]);
    }

    public function getStatsForPeriod(Package $package, $period): ?DownloadStats
    {
        // jsDelivr API does not support custom period stats, so return null
        return null;
    }

    public function getAvailablePeriods(Package $package): array
    {
        // jsDelivr only provides monthly stats in this implementation
        return ['monthly'];
    }

    public function hasCdnStats(Package $package): bool
    {
        // jsDelivr provides CDN stats for npm and composer packages
        $id = $package->getIdentifier();

        return str_starts_with($id, 'npm/') || str_starts_with($id, 'composer/');
    }
}
