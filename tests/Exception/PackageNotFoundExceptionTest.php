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

namespace PackApi\Tests\Exception;

use PackApi\Exception\PackageNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PackageNotFoundException::class)]
final class PackageNotFoundExceptionTest extends TestCase
{
    public function testExceptionCanBeInstantiated(): void
    {
        $exception = new PackageNotFoundException('Package not found');
        $this->assertInstanceOf(PackageNotFoundException::class, $exception);
        $this->assertSame('Package not found', $exception->getMessage());
    }
}
