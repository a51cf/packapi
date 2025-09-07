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

use PackApi\Exception\ValidationException;
use PackApi\Package\NpmPackage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NpmPackage::class)]
final class NpmPackageTest extends TestCase
{
    public function testConstructorSetsNameAndIdentifier(): void
    {
        $packageName = 'my-npm-package';
        $package = new NpmPackage($packageName);

        $this->assertSame($packageName, $package->getName());
        $this->assertSame($packageName, $package->getIdentifier());
    }

    public function testValidScopedPackageName(): void
    {
        $packageName = '@scope/my-package';
        $package = new NpmPackage($packageName);

        $this->assertSame($packageName, $package->getName());
        $this->assertSame($packageName, $package->getIdentifier());
    }

    public function testValidPackageNameWithDots(): void
    {
        $packageName = 'my.package.name';
        $package = new NpmPackage($packageName);

        $this->assertSame($packageName, $package->getName());
    }

    public function testValidPackageNameWithUnderscores(): void
    {
        $packageName = 'my_package_name';
        $package = new NpmPackage($packageName);

        $this->assertSame($packageName, $package->getName());
    }

    public function testEmptyNameThrowsValidationException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('NPM package name cannot be empty');

        new NpmPackage('');
    }

    public function testTooLongNameThrowsValidationException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('NPM package name cannot exceed 214 characters');

        new NpmPackage(str_repeat('a', 215));
    }

    public function testUppercaseNameThrowsValidationException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid NPM package name format');

        new NpmPackage('MyPackage');
    }

    public function testSpaceInNameThrowsValidationException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid NPM package name format');

        new NpmPackage('my package');
    }

    public function testConsecutiveDotsThrowValidationException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('NPM package name cannot contain consecutive dots');

        new NpmPackage('my..package');
    }

    public function testStartsWithDotThrowsValidationException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('NPM package name cannot start or end with a dot');

        new NpmPackage('.mypackage');
    }

    public function testEndsWithDotThrowsValidationException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('NPM package name cannot start or end with a dot');

        new NpmPackage('mypackage.');
    }

    public function testStartsWithHyphenThrowsValidationException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('NPM package name cannot start or end with a hyphen');

        new NpmPackage('-mypackage');
    }

    public function testEndsWithHyphenThrowsValidationException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('NPM package name cannot start or end with a hyphen');

        new NpmPackage('mypackage-');
    }

    public function testInvalidScopedPackageFormatThrowsValidationException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid NPM scoped package name format');

        new NpmPackage('@Invalid/Package');
    }
}
