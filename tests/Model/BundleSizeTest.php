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

use PackApi\Model\BundleSize;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BundleSize::class)]
final class BundleSizeTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $bundleSize = new BundleSize(
            name: 'react',
            version: '18.2.0',
            description: 'React is a JavaScript library for building user interfaces.',
            size: 42000,
            gzip: 13000,
            dependencyCount: 2,
            dependencySize: 15000,
            hasJSModule: true,
            hasSideEffects: false,
            isScoped: false,
            repository: 'https://github.com/facebook/react.git',
        );

        $this->assertSame('react', $bundleSize->getName());
        $this->assertSame('18.2.0', $bundleSize->getVersion());
        $this->assertSame('React is a JavaScript library for building user interfaces.', $bundleSize->getDescription());
        $this->assertSame(42000, $bundleSize->getSize());
        $this->assertSame(13000, $bundleSize->getGzipSize());
        $this->assertSame(2, $bundleSize->getDependencyCount());
        $this->assertSame(15000, $bundleSize->getDependencySize());
        $this->assertTrue($bundleSize->hasJSModule());
        $this->assertFalse($bundleSize->hasSideEffects());
        $this->assertFalse($bundleSize->isScoped());
        $this->assertSame('https://github.com/facebook/react.git', $bundleSize->getRepository());
    }

    public function testFormattedSizeMethods(): void
    {
        $bundleSize = new BundleSize(
            name: 'test-package',
            version: '1.0.0',
            description: null,
            size: 1536,      // 1.5 KB
            gzip: 512,       // 512 B
            dependencyCount: 0,
            dependencySize: 1048576, // 1 MB
            hasJSModule: false,
            hasSideEffects: false,
            isScoped: false,
            repository: null,
        );

        $this->assertSame('1.5 KB', $bundleSize->getFormattedSize());
        $this->assertSame('512 B', $bundleSize->getFormattedGzipSize());
        $this->assertSame('1 MB', $bundleSize->getFormattedDependencySize());
    }

    public function testFormattedSizeWithZeroBytes(): void
    {
        $bundleSize = new BundleSize(
            name: 'empty-package',
            version: '1.0.0',
            description: null,
            size: 0,
            gzip: 0,
            dependencyCount: 0,
            dependencySize: 0,
            hasJSModule: false,
            hasSideEffects: false,
            isScoped: false,
            repository: null,
        );

        $this->assertSame('0 B', $bundleSize->getFormattedSize());
        $this->assertSame('0 B', $bundleSize->getFormattedGzipSize());
        $this->assertSame('0 B', $bundleSize->getFormattedDependencySize());
    }

    public function testFormattedSizeWithLargeBytes(): void
    {
        $bundleSize = new BundleSize(
            name: 'large-package',
            version: '1.0.0',
            description: null,
            size: 1073741824, // 1 GB
            gzip: 268435456,  // 256 MB
            dependencyCount: 0,
            dependencySize: 2147483648, // 2 GB
            hasJSModule: false,
            hasSideEffects: false,
            isScoped: false,
            repository: null,
        );

        $this->assertSame('1 GB', $bundleSize->getFormattedSize());
        $this->assertSame('256 MB', $bundleSize->getFormattedGzipSize());
        $this->assertSame('2 GB', $bundleSize->getFormattedDependencySize());
    }

    public function testWithNullValues(): void
    {
        $bundleSize = new BundleSize(
            name: 'test',
            version: '1.0.0',
            description: null,
            size: 1000,
            gzip: 300,
            dependencyCount: 0,
            dependencySize: 0,
            hasJSModule: false,
            hasSideEffects: false,
            isScoped: false,
            repository: null,
        );

        $this->assertNull($bundleSize->getDescription());
        $this->assertNull($bundleSize->getRepository());
    }

    public function testScopedPackage(): void
    {
        $bundleSize = new BundleSize(
            name: '@scope/package',
            version: '1.0.0',
            description: 'A scoped package',
            size: 1000,
            gzip: 300,
            dependencyCount: 0,
            dependencySize: 0,
            hasJSModule: false,
            hasSideEffects: false,
            isScoped: true,
            repository: null,
        );

        $this->assertTrue($bundleSize->isScoped());
        $this->assertSame('@scope/package', $bundleSize->getName());
    }
}
