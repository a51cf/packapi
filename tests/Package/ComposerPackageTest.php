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

use PackApi\Package\ComposerPackage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ComposerPackage::class)]
final class ComposerPackageTest extends TestCase
{
    public function testConstructorSetsNameAndIdentifier(): void
    {
        $packageName = 'vendor/package';
        $package = new ComposerPackage($packageName);

        $this->assertSame($packageName, $package->getName());
        $this->assertSame($packageName, $package->getIdentifier());
    }

    public function testNameAndIdentifierAreAlwaysEqual(): void
    {
        $package = new ComposerPackage('vendor/package');
        $this->assertSame($package->getName(), $package->getIdentifier());
    }

    public function testEmptyNameIsHandled(): void
    {
        $package = new ComposerPackage('');
        $this->assertSame('', $package->getName());
        $this->assertSame('', $package->getIdentifier());
    }

    public function testDifferentNamesProduceDifferentIdentifiers(): void
    {
        $package1 = new ComposerPackage('vendor/package1');
        $package2 = new ComposerPackage('vendor/package2');
        $this->assertNotEquals($package1->getIdentifier(), $package2->getIdentifier());
    }
}
