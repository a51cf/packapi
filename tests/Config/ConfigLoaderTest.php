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

use PackApi\Config\ConfigLoader;
use PackApi\Config\Configuration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigLoader::class)]
final class ConfigLoaderTest extends TestCase
{
    public function testLoadReturnsDefaultConfigurationIfNoFileProvided(): void
    {
        $loader = new ConfigLoader();
        $config = $loader->load();

        $this->assertInstanceOf(Configuration::class, $config);
        $this->assertEmpty($config->github);
    }
}
