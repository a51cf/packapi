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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DownloadPeriod::class)]
final class DownloadPeriodTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $type = 'monthly';
        $count = 1500;
        $start = new \DateTimeImmutable('2023-06-01');
        $end = new \DateTimeImmutable('2023-06-30');

        $period = new DownloadPeriod($type, $count, $start, $end);

        $this->assertSame($type, $period->getType());
        $this->assertSame($count, $period->getCount());
        $this->assertSame($start, $period->getStart());
        $this->assertSame($end, $period->getEnd());

        // Test public readonly properties
        $this->assertSame($type, $period->type);
        $this->assertSame($count, $period->count);
        $this->assertSame($start, $period->start);
        $this->assertSame($end, $period->end);
    }

    public function testIsMethodWithMatchingType(): void
    {
        $period = new DownloadPeriod(
            'weekly',
            750,
            new \DateTimeImmutable('2023-06-01'),
            new \DateTimeImmutable('2023-06-07')
        );

        $this->assertTrue($period->is('weekly'));
    }

    public function testIsMethodWithNonMatchingType(): void
    {
        $period = new DownloadPeriod(
            'weekly',
            750,
            new \DateTimeImmutable('2023-06-01'),
            new \DateTimeImmutable('2023-06-07')
        );

        $this->assertFalse($period->is('monthly'));
        $this->assertFalse($period->is('daily'));
        $this->assertFalse($period->is('total'));
    }

    public function testWithTotalType(): void
    {
        $period = new DownloadPeriod(
            'total',
            50000,
            new \DateTimeImmutable('2020-01-01'),
            new \DateTimeImmutable('2023-06-30')
        );

        $this->assertTrue($period->is('total'));
        $this->assertFalse($period->is('monthly'));
        $this->assertSame('total', $period->getType());
        $this->assertSame(50000, $period->getCount());
    }

    public function testWithDailyType(): void
    {
        $period = new DownloadPeriod(
            'daily',
            25,
            new \DateTimeImmutable('2023-06-15'),
            new \DateTimeImmutable('2023-06-16')
        );

        $this->assertTrue($period->is('daily'));
        $this->assertFalse($period->is('weekly'));
        $this->assertSame('daily', $period->getType());
        $this->assertSame(25, $period->getCount());
    }

    public function testWithZeroCount(): void
    {
        $period = new DownloadPeriod(
            'monthly',
            0,
            new \DateTimeImmutable('2023-06-01'),
            new \DateTimeImmutable('2023-06-30')
        );

        $this->assertSame(0, $period->getCount());
        $this->assertSame('monthly', $period->getType());
        $this->assertTrue($period->is('monthly'));
    }

    public function testWithSameDateRange(): void
    {
        $date = new \DateTimeImmutable('2023-06-15');
        $period = new DownloadPeriod('daily', 10, $date, $date);

        $this->assertSame($date, $period->getStart());
        $this->assertSame($date, $period->getEnd());
        $this->assertSame(10, $period->getCount());
    }

    public function testIsMethodIsCaseSensitive(): void
    {
        $period = new DownloadPeriod(
            'Monthly',
            100,
            new \DateTimeImmutable('2023-06-01'),
            new \DateTimeImmutable('2023-06-30')
        );

        $this->assertTrue($period->is('Monthly'));
        $this->assertFalse($period->is('monthly'));
        $this->assertFalse($period->is('MONTHLY'));
    }
}
