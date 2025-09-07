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

use PackApi\Exception\NetworkException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NetworkException::class)]
final class NetworkExceptionTest extends TestCase
{
    public function testExceptionCanBeInstantiated(): void
    {
        $exception = new NetworkException('Network error', 0, new \Exception('Original'));
        $this->assertInstanceOf(NetworkException::class, $exception);
        $this->assertSame('Network error', $exception->getMessage());
        $this->assertInstanceOf(\Exception::class, $exception->getPrevious());
    }
}
