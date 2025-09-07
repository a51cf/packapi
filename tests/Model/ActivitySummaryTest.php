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

use PackApi\Model\ActivitySummary;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ActivitySummary::class)]
final class ActivitySummaryTest extends TestCase
{
    public function testConstructorAndGettersWithAllValues(): void
    {
        $lastCommit = new \DateTimeImmutable('2023-06-15 14:30:00');
        $contributors = 25;
        $openIssues = 12;
        $lastRelease = 'v2.1.0';

        $summary = new ActivitySummary($lastCommit, $contributors, $openIssues, $lastRelease);

        $this->assertSame($lastCommit, $summary->getLastCommit());
        $this->assertSame($contributors, $summary->getContributors());
        $this->assertSame($openIssues, $summary->getOpenIssues());
        $this->assertSame($lastRelease, $summary->getLastRelease());

        // Test public readonly properties
        $this->assertSame($lastCommit, $summary->lastCommit);
        $this->assertSame($contributors, $summary->contributors);
        $this->assertSame($openIssues, $summary->openIssues);
        $this->assertSame($lastRelease, $summary->lastRelease);
    }

    public function testConstructorWithDefaultValues(): void
    {
        $summary = new ActivitySummary();

        $this->assertNull($summary->getLastCommit());
        $this->assertSame(0, $summary->getContributors());
        $this->assertSame(0, $summary->getOpenIssues());
        $this->assertNull($summary->getLastRelease());

        // Test public readonly properties
        $this->assertNull($summary->lastCommit);
        $this->assertSame(0, $summary->contributors);
        $this->assertSame(0, $summary->openIssues);
        $this->assertNull($summary->lastRelease);
    }

    public function testConstructorWithPartialValues(): void
    {
        $lastCommit = new \DateTimeImmutable('2023-01-01 00:00:00');
        $contributors = 5;

        $summary = new ActivitySummary($lastCommit, $contributors);

        $this->assertSame($lastCommit, $summary->getLastCommit());
        $this->assertSame($contributors, $summary->getContributors());
        $this->assertSame(0, $summary->getOpenIssues());
        $this->assertNull($summary->getLastRelease());
    }

    public function testConstructorWithOnlyOpenIssues(): void
    {
        $openIssues = 42;

        $summary = new ActivitySummary(null, 0, $openIssues);

        $this->assertNull($summary->getLastCommit());
        $this->assertSame(0, $summary->getContributors());
        $this->assertSame($openIssues, $summary->getOpenIssues());
        $this->assertNull($summary->getLastRelease());
    }

    public function testConstructorWithOnlyLastRelease(): void
    {
        $lastRelease = 'v1.0.0-beta.1';

        $summary = new ActivitySummary(null, 0, 0, $lastRelease);

        $this->assertNull($summary->getLastCommit());
        $this->assertSame(0, $summary->getContributors());
        $this->assertSame(0, $summary->getOpenIssues());
        $this->assertSame($lastRelease, $summary->getLastRelease());
    }
}
