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

namespace PackApi\Tests\Inspector;

use PackApi\Inspector\SecurityInspector;
use PackApi\Model\SecurityAdvisory;
use PackApi\Package\Package;
use PackApi\Provider\SecurityProviderInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SecurityInspector::class)]
final class SecurityInspectorTest extends TestCase
{
    public function testGetSecurityAdvisoriesReturnsNullWhenNoProviderSupports(): void
    {
        $package = $this->createStub(Package::class);

        $provider = new class implements SecurityProviderInterface {
            public function supports(Package $package): bool
            {
                return false;
            }

            public function getSecurityAdvisories(Package $package): array
            {
                throw new \LogicException('Should not be called');
            }
        };

        $inspector = new SecurityInspector([$provider]);

        $this->assertNull($inspector->getSecurityAdvisories($package));
    }

    public function testGetSecurityAdvisoriesReturnsAdvisoriesWhenProviderSupports(): void
    {
        $package = $this->createStub(Package::class);
        $advisory = new SecurityAdvisory('1', 'title', 'low', 'link');
        $expected = [$advisory];

        $provider = new class($expected) implements SecurityProviderInterface {
            public function __construct(private array $advisories)
            {
            }

            public function supports(Package $package): bool
            {
                return true;
            }

            public function getSecurityAdvisories(Package $package): array
            {
                return $this->advisories;
            }
        };

        $inspector = new SecurityInspector([$provider]);

        $this->assertSame($expected, $inspector->getSecurityAdvisories($package));
    }
}
