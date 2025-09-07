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

namespace PackApi\Tests\Package;

use PackApi\Package\Package;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Package::class)]
final class PackageTest extends TestCase
{
    private function createConcretePackage(string $name, string $identifier): Package
    {
        return new class($name, $identifier) extends Package {
        };
    }

    public function testGetNameReturnsCorrectName(): void
    {
        $package = $this->createConcretePackage('test-name', 'test-identifier');
        $this->assertSame('test-name', $package->getName());
    }

    public function testGetIdentifierReturnsCorrectIdentifier(): void
    {
        $package = $this->createConcretePackage('test-name', 'test-identifier');
        $this->assertSame('test-identifier', $package->getIdentifier());
    }

    public function testRepositoryUrlCanBeSetAndRetrieved(): void
    {
        $package = $this->createConcretePackage('test-name', 'test-identifier');
        $package->setRepositoryUrl('https://example.com/repo');
        $this->assertSame('https://example.com/repo', $package->getRepositoryUrl());
    }

    public function testRepositoryUrlDefaultsToNull(): void
    {
        $package = $this->createConcretePackage('test-name', 'test-identifier');
        $this->assertNull($package->getRepositoryUrl());
    }

    public function testRepositoryUrlCanBeSetToNull(): void
    {
        $package = $this->createConcretePackage('test-name', 'test-identifier');
        $package->setRepositoryUrl(null);
        $this->assertNull($package->getRepositoryUrl());
    }
}
