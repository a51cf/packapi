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
final class ConfigLoaderCustomFileTest extends TestCase
{
    public function testLoadMergesConfigurationFromFile(): void
    {
        $yaml = "github:\n  token: ABC123\ncache:\n  ttl: 7200";
        $tmpFile = tempnam(sys_get_temp_dir(), 'cfg');
        file_put_contents($tmpFile, $yaml);

        $loader = new ConfigLoader();
        $config = $loader->load($tmpFile);

        $this->assertInstanceOf(Configuration::class, $config);
        $this->assertSame(['token' => 'ABC123'], $config->github);
        $this->assertSame(['ttl' => 7200, 'type' => 'memory'], $config->cache);

        unlink($tmpFile);
    }
}
