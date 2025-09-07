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

use PackApi\Exception\RateLimitException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RateLimitException::class)]
final class RateLimitExceptionTest extends TestCase
{
    public function testExceptionCanBeInstantiated(): void
    {
        $exception = new RateLimitException('Rate limit exceeded');
        $this->assertInstanceOf(RateLimitException::class, $exception);
        $this->assertSame('Rate limit exceeded', $exception->getMessage());
    }
}
