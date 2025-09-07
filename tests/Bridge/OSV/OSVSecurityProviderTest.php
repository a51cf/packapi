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

namespace PackApi\Tests\Bridge\OSV;

use PackApi\Bridge\OSV\OSVApiClient;
use PackApi\Bridge\OSV\OSVSecurityProvider;
use PackApi\Model\SecurityAdvisory;
use PackApi\Package\ComposerPackage;
use PackApi\Package\NpmPackage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(OSVSecurityProvider::class)]
final class OSVSecurityProviderTest extends TestCase
{
    private OSVApiClient|MockObject $osvApiClient;
    private OSVSecurityProvider $provider;

    protected function setUp(): void
    {
        $this->osvApiClient = $this->createMock(OSVApiClient::class);
        $this->provider = new OSVSecurityProvider($this->osvApiClient);
    }

    public function testSupportsComposerPackage(): void
    {
        $package = new ComposerPackage('vendor/package');

        $this->assertTrue($this->provider->supports($package));
    }

    public function testSupportsNpmPackage(): void
    {
        $package = new NpmPackage('test-package');

        $this->assertTrue($this->provider->supports($package));
    }

    public function testGetSecurityAdvisoriesReturnsEmptyArrayWhenNoVulnerabilities(): void
    {
        $package = new NpmPackage('test-package');

        $this->osvApiClient
            ->expects($this->once())
            ->method('queryVulnerabilities')
            ->with('npm', 'test-package')
            ->willReturn(['vulns' => []]);

        $result = $this->provider->getSecurityAdvisories($package);

        $this->assertSame([], $result);
    }

    public function testGetSecurityAdvisoriesReturnsNullWhenApiReturnsNull(): void
    {
        $package = new NpmPackage('test-package');

        $this->osvApiClient
            ->expects($this->once())
            ->method('queryVulnerabilities')
            ->with('npm', 'test-package')
            ->willReturn(null);

        $result = $this->provider->getSecurityAdvisories($package);

        $this->assertSame([], $result);
    }

    public function testGetSecurityAdvisoriesReturnsAdvisories(): void
    {
        $package = new ComposerPackage('vendor/package');
        $vulnerabilityData = [
            'vulns' => [
                [
                    'id' => 'OSV-2023-1234',
                    'summary' => 'Test vulnerability',
                    'severity' => [
                        ['score' => 8.5],
                    ],
                ],
                [
                    'id' => 'OSV-2023-5678',
                    'summary' => 'Another vulnerability',
                    'database_specific' => [
                        'severity' => 'low',
                    ],
                ],
            ],
        ];

        $this->osvApiClient
            ->expects($this->once())
            ->method('queryVulnerabilities')
            ->with('Packagist', 'vendor/package')
            ->willReturn($vulnerabilityData);

        $result = $this->provider->getSecurityAdvisories($package);

        $this->assertCount(2, $result);
        $this->assertInstanceOf(SecurityAdvisory::class, $result[0]);
        $this->assertSame('OSV-2023-1234', $result[0]->getId());
        $this->assertSame('Test vulnerability', $result[0]->getTitle());
        $this->assertSame('HIGH', $result[0]->getSeverity());
        $this->assertSame('https://osv.dev/vulnerability/OSV-2023-1234', $result[0]->getLink());

        $this->assertSame('OSV-2023-5678', $result[1]->getId());
        $this->assertSame('LOW', $result[1]->getSeverity());
    }

    public function testGetSecurityAdvisoriesForVersionCallsApiWithVersion(): void
    {
        $package = new NpmPackage('test-package');
        $version = '1.0.0';

        $this->osvApiClient
            ->expects($this->once())
            ->method('queryVulnerabilities')
            ->with('npm', 'test-package', '1.0.0')
            ->willReturn(['vulns' => []]);

        $result = $this->provider->getSecurityAdvisoriesForVersion($package, $version);

        $this->assertSame([], $result);
    }

    public function testIsVulnerabilityRelevantReturnsTrueWhenPackageMatches(): void
    {
        $package = new NpmPackage('test-package');
        $vulnId = 'OSV-2023-1234';
        $vulnerabilityData = [
            'id' => $vulnId,
            'affected' => [
                [
                    'package' => [
                        'ecosystem' => 'npm',
                        'name' => 'test-package',
                    ],
                ],
            ],
        ];

        $this->osvApiClient
            ->expects($this->once())
            ->method('getVulnerabilityById')
            ->with($vulnId)
            ->willReturn($vulnerabilityData);

        $result = $this->provider->isVulnerabilityRelevant($package, $vulnId);

        $this->assertTrue($result);
    }

    public function testIsVulnerabilityRelevantReturnsFalseWhenPackageDoesNotMatch(): void
    {
        $package = new NpmPackage('test-package');
        $vulnId = 'OSV-2023-1234';
        $vulnerabilityData = [
            'id' => $vulnId,
            'affected' => [
                [
                    'package' => [
                        'ecosystem' => 'npm',
                        'name' => 'other-package',
                    ],
                ],
            ],
        ];

        $this->osvApiClient
            ->expects($this->once())
            ->method('getVulnerabilityById')
            ->with($vulnId)
            ->willReturn($vulnerabilityData);

        $result = $this->provider->isVulnerabilityRelevant($package, $vulnId);

        $this->assertFalse($result);
    }

    public function testGetVulnerabilityDetailsReturnsData(): void
    {
        $vulnId = 'OSV-2023-1234';
        $vulnerabilityData = [
            'id' => $vulnId,
            'summary' => 'Test vulnerability',
            'details' => 'Detailed description',
        ];

        $this->osvApiClient
            ->expects($this->once())
            ->method('getVulnerabilityById')
            ->with($vulnId)
            ->willReturn($vulnerabilityData);

        $result = $this->provider->getVulnerabilityDetails($vulnId);

        $this->assertSame($vulnerabilityData, $result);
    }

    public function testGetSecurityAdvisoriesHandlesExceptionGracefully(): void
    {
        $package = new NpmPackage('test-package');

        $this->osvApiClient
            ->expects($this->once())
            ->method('queryVulnerabilities')
            ->willThrowException(new \Exception('API error'));

        $result = $this->provider->getSecurityAdvisories($package);

        $this->assertSame([], $result);
    }
}
