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

use PackApi\Exception\UnsupportedPackageException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UnsupportedPackageException::class)]
final class UnsupportedPackageExceptionTest extends TestCase
{
    public function testExceptionMessage(): void
    {
        $packageName = 'test/package';
        $exception = new UnsupportedPackageException($packageName);

        $this->assertSame(
            sprintf('Unsupported package type "%s".', $packageName),
            $exception->getMessage()
        );
    }
}
