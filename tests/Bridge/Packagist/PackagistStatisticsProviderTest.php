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

namespace PackApi\Tests\Bridge\Packagist;

use PackApi\Bridge\Packagist\PackagistStatisticsProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PackagistStatisticsProvider::class)]
final class PackagistStatisticsProviderTest extends TestCase
{
    public function testClassExistsAndIsInstantiable(): void
    {
        $this->assertTrue(class_exists(PackagistStatisticsProvider::class));
        $provider = new PackagistStatisticsProvider();
        $this->assertInstanceOf(PackagistStatisticsProvider::class, $provider);
    }

    public function testSupportsComposerPackagesOnly(): void
    {
        $this->markTestSkipped('Add supports() behavior once implemented.');
    }

    public function testGetStatsAggregatesHistoricalDownloads(): void
    {
        $this->markTestSkipped('Implement getStats() using Packagist historical stats.');
    }

    public function testAvailablePeriods(): void
    {
        $this->markTestSkipped('Expose available periods after implementation.');
    }
}
