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

use PackApi\Exception\ValidationException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ValidationException::class)]
final class ValidationExceptionTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Test message');
        throw new ValidationException('Test message');
    }

    public function testExceptionExtendsInvalidArgumentException(): void
    {
        $exception = new ValidationException('Test message');
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
    }
}
