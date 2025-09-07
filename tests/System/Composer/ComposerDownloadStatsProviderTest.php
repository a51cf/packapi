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

namespace PackApi\Tests\System\Composer;

use PackApi\Bridge\Packagist\PackagistApiClient;
use PackApi\Model\DownloadPeriod;
use PackApi\Package\ComposerPackage;
use PackApi\System\Composer\ComposerDownloadStatsProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ComposerDownloadStatsProviderTest extends TestCase
{
    public function testGetStatsReturnsPeriods(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getContent')->willReturn(json_encode([
            'package' => [
                'downloads' => [
                    'total' => 1000,
                    'monthly' => 100,
                    'daily' => 10,
                ],
            ],
        ]));

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->willReturn($response);

        $client = new PackagistApiClient($httpClient);
        $provider = new ComposerDownloadStatsProvider($client);
        $package = new ComposerPackage('foo/bar');
        $stats = $provider->getStats($package);
        $this->assertNotNull($stats);
        $this->assertInstanceOf(\PackApi\Model\DownloadStats::class, $stats);
        $this->assertInstanceOf(DownloadPeriod::class, $stats->get('total'));
        $this->assertInstanceOf(DownloadPeriod::class, $stats->get('monthly'));
        $this->assertInstanceOf(DownloadPeriod::class, $stats->get('daily'));
        $this->assertSame(1000, $stats->get('total')->getCount());
        $this->assertSame(100, $stats->get('monthly')->getCount());
        $this->assertSame(10, $stats->get('daily')->getCount());
    }

    public function testGetStatsReturnsNullIfNoDownloads(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getContent')->willReturn(json_encode(['package' => []]));

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->willReturn($response);

        $client = new PackagistApiClient($httpClient);
        $provider = new ComposerDownloadStatsProvider($client);
        $package = new ComposerPackage('foo/bar');

        $this->assertNull($provider->getStats($package));
    }

    public function testHasCdnStats(): void
    {
        $client = new PackagistApiClient($this->createMock(HttpClientInterface::class));
        $provider = new ComposerDownloadStatsProvider($client);
        $package = new ComposerPackage('foo/bar');

        $this->assertFalse($provider->hasCdnStats($package));
    }
}
