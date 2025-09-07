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
final class ApiExceptionDefaultsTest extends TestCase
{
    public function testDefaultsAreNullOrZero(): void
    {
        $exception = new ApiException();

        $this->assertSame('', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->httpCode);
        $this->assertNull($exception->responseData);
        $this->assertNull($exception->getPrevious());
    }
}
