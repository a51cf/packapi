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

namespace PackApi\Tests\Config;

use PackApi\Config\Configuration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Configuration::class)]
final class ConfigurationTest extends TestCase
{
    public function testDefaultValuesAreSetCorrectly(): void
    {
        $config = new Configuration();

        $this->assertEmpty($config->github);
        $this->assertEmpty($config->packagist);
        $this->assertEmpty($config->npm);
        $this->assertEmpty($config->jsdelivr);
        $this->assertSame(['ttl' => 3600, 'type' => 'memory'], $config->cache);
        $this->assertSame(['maxAttempts' => 60, 'period' => 3600], $config->rateLimit);
        $this->assertSame(['file' => 'php://stdout'], $config->logger);
    }
}
