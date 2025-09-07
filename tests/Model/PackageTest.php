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

use PackApi\Model\Package;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Package::class)]
final class PackageTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $vendor = 'symfony';
        $name = 'console';

        $package = new Package($vendor, $name);

        $this->assertSame($vendor, $package->vendor);
        $this->assertSame($name, $package->name);
    }

    public function testGetFullName(): void
    {
        $vendor = 'symfony';
        $name = 'http-foundation';

        $package = new Package($vendor, $name);

        $this->assertSame('symfony/http-foundation', $package->getFullName());
    }

    public function testGetFullNameWithDifferentVendors(): void
    {
        $testCases = [
            ['vendor' => 'laravel', 'name' => 'framework', 'expected' => 'laravel/framework'],
            ['vendor' => 'doctrine', 'name' => 'orm', 'expected' => 'doctrine/orm'],
            ['vendor' => 'phpunit', 'name' => 'phpunit', 'expected' => 'phpunit/phpunit'],
            ['vendor' => 'monolog', 'name' => 'monolog', 'expected' => 'monolog/monolog'],
        ];

        foreach ($testCases as $testCase) {
            $package = new Package($testCase['vendor'], $testCase['name']);

            $this->assertSame($testCase['expected'], $package->getFullName());
            $this->assertSame($testCase['vendor'], $package->vendor);
            $this->assertSame($testCase['name'], $package->name);
        }
    }

    public function testConstructorWithSpecialCharacters(): void
    {
        $vendor = 'vendor-with-hyphens';
        $name = 'package_with_underscores';

        $package = new Package($vendor, $name);

        $this->assertSame($vendor, $package->vendor);
        $this->assertSame($name, $package->name);
        $this->assertSame('vendor-with-hyphens/package_with_underscores', $package->getFullName());
    }

    public function testConstructorWithNumericCharacters(): void
    {
        $vendor = 'vendor123';
        $name = 'package456';

        $package = new Package($vendor, $name);

        $this->assertSame($vendor, $package->vendor);
        $this->assertSame($name, $package->name);
        $this->assertSame('vendor123/package456', $package->getFullName());
    }

    public function testConstructorWithMixedCase(): void
    {
        $vendor = 'VendorName';
        $name = 'PackageName';

        $package = new Package($vendor, $name);

        $this->assertSame($vendor, $package->vendor);
        $this->assertSame($name, $package->name);
        $this->assertSame('VendorName/PackageName', $package->getFullName());
    }

    public function testConstructorWithSingleCharacterNames(): void
    {
        $vendor = 'a';
        $name = 'b';

        $package = new Package($vendor, $name);

        $this->assertSame($vendor, $package->vendor);
        $this->assertSame($name, $package->name);
        $this->assertSame('a/b', $package->getFullName());
    }

    public function testConstructorWithLongNames(): void
    {
        $vendor = str_repeat('long-vendor-name', 10);
        $name = str_repeat('long-package-name', 10);

        $package = new Package($vendor, $name);

        $this->assertSame($vendor, $package->vendor);
        $this->assertSame($name, $package->name);
        $this->assertSame($vendor.'/'.$name, $package->getFullName());
    }

    public function testConstructorWithEmptyStrings(): void
    {
        $vendor = '';
        $name = '';

        $package = new Package($vendor, $name);

        $this->assertSame('', $package->vendor);
        $this->assertSame('', $package->name);
        $this->assertSame('/', $package->getFullName());
    }

    public function testPublicReadonlyProperties(): void
    {
        $vendor = 'test-vendor';
        $name = 'test-package';

        $package = new Package($vendor, $name);

        // Verify properties are accessible directly
        $this->assertSame($vendor, $package->vendor);
        $this->assertSame($name, $package->name);
    }

    public function testGetFullNameWithDotsInNames(): void
    {
        $vendor = 'vendor.with.dots';
        $name = 'package.with.dots';

        $package = new Package($vendor, $name);

        $this->assertSame('vendor.with.dots/package.with.dots', $package->getFullName());
    }

    public function testGetFullNameFormatIsConsistent(): void
    {
        $testCases = [
            ['vendor' => 'a', 'name' => 'b'],
            ['vendor' => 'very-long-vendor-name', 'name' => 'very-long-package-name'],
            ['vendor' => 'Vendor', 'name' => 'Package'],
            ['vendor' => 'vendor123', 'name' => 'package456'],
        ];

        foreach ($testCases as $testCase) {
            $package = new Package($testCase['vendor'], $testCase['name']);
            $fullName = $package->getFullName();

            // Verify format is always vendor/name
            $this->assertStringContainsString('/', $fullName);
            $this->assertSame($testCase['vendor'].'/'.$testCase['name'], $fullName);

            // Verify the slash appears exactly once
            $this->assertSame(1, substr_count($fullName, '/'));
        }
    }
}
