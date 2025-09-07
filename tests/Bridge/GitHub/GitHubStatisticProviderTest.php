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

namespace PackApi\Tests\Bridge\GitHub;

use PackApi\Bridge\GitHub\GitHubApiClient;
use PackApi\Bridge\GitHub\GitHubStatisticProvider;
use PackApi\Model\DownloadStats;
use PackApi\Package\ComposerPackage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(GitHubStatisticProvider::class)]
final class GitHubStatisticProviderTest extends TestCase
{
    public function testGetStatsReturnsDownloadStats(): void
    {
        $responses = [
            new MockResponse(json_encode(['stargazers_count' => 5])),
            new MockResponse(json_encode(['stargazers_count' => 5])),
            new MockResponse(json_encode([['commit' => ['committer' => ['date' => '2024-01-01T00:00:00Z']]]])),
            new MockResponse(json_encode([[]])),
            new MockResponse(json_encode([[]])),
        ];
        $client = new GitHubApiClient(new MockHttpClient($responses));

        $provider = new GitHubStatisticProvider($client);
        $pkg = new ComposerPackage('owner/repo');
        $pkg->setRepositoryUrl('https://github.com/owner/repo');

        $stats = $provider->getStats($pkg);

        $this->assertInstanceOf(DownloadStats::class, $stats);
        $this->assertTrue($stats->has('monthly'));
    }
}
