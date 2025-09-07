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

namespace PackApi\Tests\Bridge\Npm;

use PackApi\Bridge\Npm\NpmApiClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(NpmApiClient::class)]
class NpmApiClientTest extends TestCase
{
    public function testFetchPackageInfoReturnsPackageData(): void
    {
        $response = new MockResponse(json_encode(['name' => 'example-package']), [
            'http_code' => 200,
        ]);
        $registryClient = new MockHttpClient($response);
        $statsClient = new MockHttpClient();

        $client = new NpmApiClient($registryClient, $statsClient);

        $result = $client->fetchPackageInfo('example-package');

        $this->assertSame(['name' => 'example-package'], $result);
    }

    public function testFetchPackageInfoReturnsNullForNotFound(): void
    {
        $response = new MockResponse('', [
            'http_code' => 404,
        ]);
        $registryClient = new MockHttpClient($response);
        $statsClient = new MockHttpClient();

        $client = new NpmApiClient($registryClient, $statsClient);

        $result = $client->fetchPackageInfo('nonexistent-package');

        $this->assertNull($result);
    }

    public function testFetchPackageInfoThrowsNetworkExceptionOnError(): void
    {
        $registryClient = new MockHttpClient(function () {
            throw new \Exception('Network error');
        });
        $statsClient = new MockHttpClient();

        $client = new NpmApiClient($registryClient, $statsClient);

        $this->expectException(\Exception::class);
        $client->fetchPackageInfo('example-package');
    }

    public function testFetchDownloadStatsReturnsStatsData(): void
    {
        $response = new MockResponse(json_encode(['downloads' => 12345]), [
            'http_code' => 200,
        ]);
        $statsClient = new MockHttpClient($response);
        $registryClient = new MockHttpClient();

        $client = new NpmApiClient($registryClient, $statsClient);

        $result = $client->fetchDownloadStats('example-package');

        $this->assertSame(['downloads' => 12345], $result);
    }

    public function testFetchDownloadStatsReturnsNullForNotFound(): void
    {
        $response = new MockResponse('', [
            'http_code' => 404,
        ]);
        $statsClient = new MockHttpClient($response);
        $registryClient = new MockHttpClient();

        $client = new NpmApiClient($registryClient, $statsClient);

        $result = $client->fetchDownloadStats('nonexistent-package');

        $this->assertNull($result);
    }

    public function testFetchDownloadStatsThrowsNetworkExceptionOnError(): void
    {
        $statsClient = new MockHttpClient(function () {
            throw new \Exception('Network error');
        });
        $registryClient = new MockHttpClient();

        $client = new NpmApiClient($registryClient, $statsClient);

        $this->expectException(\Exception::class);
        $client->fetchDownloadStats('example-package');
    }
}
