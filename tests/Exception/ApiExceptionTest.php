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

use PackApi\Exception\ApiException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ApiException::class)]
final class ApiExceptionTest extends TestCase
{
    public function testExceptionCanBeInstantiated(): void
    {
        $exception = new ApiException('Test message', 123, null, 400, ['error' => 'bad request']);
        $this->assertInstanceOf(ApiException::class, $exception);
        $this->assertSame('Test message', $exception->getMessage());
        $this->assertSame(123, $exception->getCode());
        $this->assertSame(400, $exception->httpCode);
        $this->assertSame(['error' => 'bad request'], $exception->responseData);
    }
}
