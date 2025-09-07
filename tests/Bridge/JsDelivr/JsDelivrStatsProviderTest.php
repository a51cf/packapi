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

namespace PackApi\Tests\Bridge\JsDelivr;

use PackApi\Bridge\JsDelivr\JsDelivrApiClient;
use PackApi\Bridge\JsDelivr\JsDelivrStatsProvider;
use PackApi\Model\DownloadStats;
use PackApi\Package\Package;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(JsDelivrStatsProvider::class)]
final class JsDelivrStatsProviderTest extends TestCase
{
    private function makePackage(string $id): Package
    {
        return new class('name', $id) extends Package {};
    }

    public function testSupportsPrefixes(): void
    {
        $client = new JsDelivrApiClient(new MockHttpClient());
        $provider = new JsDelivrStatsProvider($client);

        $this->assertTrue($provider->supports($this->makePackage('npm/pkg')));
        $this->assertTrue($provider->supports($this->makePackage('composer/pkg')));
        $this->assertFalse($provider->supports($this->makePackage('gh/pkg')));
    }

    public function testGetStatsReturnsNullWhenNoHits(): void
    {
        $http = new MockHttpClient([
            new MockResponse('{}', ['http_code' => 200]),
        ]);
        $provider = new JsDelivrStatsProvider(new JsDelivrApiClient($http));
        $package = $this->makePackage('npm/pkg');

        $this->assertNull($provider->getStats($package));
    }

    public function testGetStatsBuildsDownloadStats(): void
    {
        $http = new MockHttpClient([
            new MockResponse('{"hits":42}', ['http_code' => 200]),
        ]);
        $provider = new JsDelivrStatsProvider(new JsDelivrApiClient($http));
        $package = $this->makePackage('npm/pkg');

        $stats = $provider->getStats($package);

        $this->assertInstanceOf(DownloadStats::class, $stats);
        $period = $stats->get('monthly');
        $this->assertNotNull($period);
        $this->assertSame('monthly', $period->getType());
        $this->assertSame(42, $period->getCount());
        $this->assertLessThan($period->getEnd(), $period->getStart());
    }

    public function testAvailablePeriods(): void
    {
        $provider = new JsDelivrStatsProvider(new JsDelivrApiClient(new MockHttpClient()));
        $this->assertSame(['monthly'], $provider->getAvailablePeriods($this->makePackage('npm/pkg')));
    }

    public function testGetStatsForPeriodReturnsNull(): void
    {
        $provider = new JsDelivrStatsProvider(new JsDelivrApiClient(new MockHttpClient()));
        $this->assertNull($provider->getStatsForPeriod($this->makePackage('npm/pkg'), 'daily'));
    }

    public function testHasCdnStats(): void
    {
        $provider = new JsDelivrStatsProvider(new JsDelivrApiClient(new MockHttpClient()));
        $this->assertTrue($provider->hasCdnStats($this->makePackage('npm/pkg')));
        $this->assertFalse($provider->hasCdnStats($this->makePackage('gh/pkg')));
    }
}
