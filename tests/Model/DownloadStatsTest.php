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

namespace PackApi\Tests\Model;

use PackApi\Model\DownloadPeriod;
use PackApi\Model\DownloadStats;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DownloadStats::class)]
final class DownloadStatsTest extends TestCase
{
    public function testConstructorAndGettersWithAllValues(): void
    {
        $periods = [
            'daily' => new DownloadPeriod('daily', 50, new \DateTimeImmutable('2023-06-15'), new \DateTimeImmutable('2023-06-16')),
            'weekly' => new DownloadPeriod('weekly', 350, new \DateTimeImmutable('2023-06-09'), new \DateTimeImmutable('2023-06-15')),
            'monthly' => new DownloadPeriod('monthly', 1500, new \DateTimeImmutable('2023-06-01'), new \DateTimeImmutable('2023-06-30')),
        ];
        $cdnRequests = 25000;
        $cdnBandwidth = 1024000;
        $lastUpdated = new \DateTimeImmutable('2023-06-16 10:30:00');

        $stats = new DownloadStats($periods, $cdnRequests, $cdnBandwidth, $lastUpdated);

        $this->assertSame($periods, $stats->getPeriods());
        $this->assertSame($cdnRequests, $stats->getCdnRequests());
        $this->assertSame($cdnBandwidth, $stats->getCdnBandwidth());
        $this->assertSame($lastUpdated, $stats->getLastUpdated());

        // Test public readonly properties
        $this->assertSame($periods, $stats->periods);
        $this->assertSame($cdnRequests, $stats->cdnRequests);
        $this->assertSame($cdnBandwidth, $stats->cdnBandwidth);
        $this->assertSame($lastUpdated, $stats->lastUpdated);
    }

    public function testConstructorWithDefaultValues(): void
    {
        $stats = new DownloadStats();

        $this->assertSame([], $stats->getPeriods());
        $this->assertNull($stats->getCdnRequests());
        $this->assertNull($stats->getCdnBandwidth());
        $this->assertNull($stats->getLastUpdated());

        // Test public readonly properties
        $this->assertSame([], $stats->periods);
        $this->assertNull($stats->cdnRequests);
        $this->assertNull($stats->cdnBandwidth);
        $this->assertNull($stats->lastUpdated);
    }

    public function testGetMethodWithExistingKey(): void
    {
        $dailyPeriod = new DownloadPeriod('daily', 75, new \DateTimeImmutable('2023-06-15'), new \DateTimeImmutable('2023-06-16'));
        $weeklyPeriod = new DownloadPeriod('weekly', 525, new \DateTimeImmutable('2023-06-09'), new \DateTimeImmutable('2023-06-15'));

        $periods = [
            'daily' => $dailyPeriod,
            'weekly' => $weeklyPeriod,
        ];

        $stats = new DownloadStats($periods);

        $this->assertSame($dailyPeriod, $stats->get('daily'));
        $this->assertSame($weeklyPeriod, $stats->get('weekly'));
    }

    public function testGetMethodWithNonExistingKey(): void
    {
        $periods = [
            'daily' => new DownloadPeriod('daily', 75, new \DateTimeImmutable('2023-06-15'), new \DateTimeImmutable('2023-06-16')),
        ];

        $stats = new DownloadStats($periods);

        $this->assertNull($stats->get('weekly'));
        $this->assertNull($stats->get('monthly'));
        $this->assertNull($stats->get('total'));
        $this->assertNull($stats->get('nonexistent'));
    }

    public function testHasMethodWithExistingKey(): void
    {
        $periods = [
            'daily' => new DownloadPeriod('daily', 100, new \DateTimeImmutable('2023-06-15'), new \DateTimeImmutable('2023-06-16')),
            'monthly' => new DownloadPeriod('monthly', 3000, new \DateTimeImmutable('2023-06-01'), new \DateTimeImmutable('2023-06-30')),
        ];

        $stats = new DownloadStats($periods);

        $this->assertTrue($stats->has('daily'));
        $this->assertTrue($stats->has('monthly'));
    }

    public function testHasMethodWithNonExistingKey(): void
    {
        $periods = [
            'daily' => new DownloadPeriod('daily', 100, new \DateTimeImmutable('2023-06-15'), new \DateTimeImmutable('2023-06-16')),
        ];

        $stats = new DownloadStats($periods);

        $this->assertFalse($stats->has('weekly'));
        $this->assertFalse($stats->has('monthly'));
        $this->assertFalse($stats->has('total'));
        $this->assertFalse($stats->has(''));
    }

    public function testConstructorWithOnlyPeriods(): void
    {
        $periods = [
            'total' => new DownloadPeriod('total', 50000, new \DateTimeImmutable('2020-01-01'), new \DateTimeImmutable('2023-06-30')),
        ];

        $stats = new DownloadStats($periods);

        $this->assertSame($periods, $stats->getPeriods());
        $this->assertNull($stats->getCdnRequests());
        $this->assertNull($stats->getCdnBandwidth());
        $this->assertNull($stats->getLastUpdated());
        $this->assertTrue($stats->has('total'));
        $this->assertInstanceOf(DownloadPeriod::class, $stats->get('total'));
    }

    public function testConstructorWithEmptyPeriodsAndCdnData(): void
    {
        $cdnRequests = 10000;
        $cdnBandwidth = 512000;

        $stats = new DownloadStats([], $cdnRequests, $cdnBandwidth);

        $this->assertSame([], $stats->getPeriods());
        $this->assertSame($cdnRequests, $stats->getCdnRequests());
        $this->assertSame($cdnBandwidth, $stats->getCdnBandwidth());
        $this->assertNull($stats->getLastUpdated());
        $this->assertFalse($stats->has('daily'));
        $this->assertNull($stats->get('daily'));
    }

    public function testConstructorWithZeroCdnValues(): void
    {
        $stats = new DownloadStats([], 0, 0);

        $this->assertSame(0, $stats->getCdnRequests());
        $this->assertSame(0, $stats->getCdnBandwidth());
    }

    public function testGetPeriodsReturnsCorrectArray(): void
    {
        $periods = [
            'daily' => new DownloadPeriod('daily', 25, new \DateTimeImmutable('2023-06-15'), new \DateTimeImmutable('2023-06-16')),
            'weekly' => new DownloadPeriod('weekly', 175, new \DateTimeImmutable('2023-06-09'), new \DateTimeImmutable('2023-06-15')),
        ];

        $stats = new DownloadStats($periods);

        $this->assertCount(2, $stats->getPeriods());
        $this->assertArrayHasKey('daily', $stats->getPeriods());
        $this->assertArrayHasKey('weekly', $stats->getPeriods());
        $this->assertInstanceOf(DownloadPeriod::class, $stats->getPeriods()['daily']);
        $this->assertInstanceOf(DownloadPeriod::class, $stats->getPeriods()['weekly']);
    }
}
