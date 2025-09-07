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

namespace PackApi\Tests\Model;

use PackApi\Model\Version;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Version::class)]
final class VersionTest extends TestCase
{
    public function testConstructorWithAllValues(): void
    {
        $url = 'https://github.com/symfony/symfony/releases/tag/v6.3.0';
        $type = 'git';

        $version = new Version($url, $type);

        $this->assertSame($url, $version->url);
        $this->assertSame($type, $version->type);
    }

    public function testConstructorWithUrlOnly(): void
    {
        $url = 'https://github.com/laravel/laravel/releases/tag/v10.0.0';

        $version = new Version($url);

        $this->assertSame($url, $version->url);
        $this->assertSame('git', $version->type); // default value
    }

    public function testConstructorWithDifferentTypes(): void
    {
        $testCases = [
            ['url' => 'https://github.com/user/repo/releases/tag/v1.0.0', 'type' => 'git'],
            ['url' => 'https://bitbucket.org/user/repo/downloads/v2.1.0.tar.gz', 'type' => 'hg'],
            ['url' => 'https://registry.npmjs.org/package/-/package-3.0.0.tgz', 'type' => 'npm'],
            ['url' => 'https://packagist.org/packages/vendor/package#1.5.0', 'type' => 'composer'],
        ];

        foreach ($testCases as $testCase) {
            $version = new Version($testCase['url'], $testCase['type']);

            $this->assertSame($testCase['url'], $version->url);
            $this->assertSame($testCase['type'], $version->type);
        }
    }

    public function testConstructorWithTagUrl(): void
    {
        $url = 'https://github.com/facebook/react/releases/tag/v18.2.0';
        $type = 'git';

        $version = new Version($url, $type);

        $this->assertSame($url, $version->url);
        $this->assertSame($type, $version->type);
    }

    public function testConstructorWithNullType(): void
    {
        $url = 'https://example.com/package/v1.0.0';

        $version = new Version($url, null);

        $this->assertSame($url, $version->url);
        $this->assertNull($version->type);
    }

    public function testConstructorWithEmptyStringType(): void
    {
        $url = 'https://example.com/package/v1.0.0';

        $version = new Version($url, '');

        $this->assertSame($url, $version->url);
        $this->assertSame('', $version->type);
    }

    public function testConstructorWithSemverUrl(): void
    {
        $testCases = [
            'https://github.com/user/repo/releases/tag/v1.0.0',
            'https://github.com/user/repo/releases/tag/v2.1.3',
            'https://github.com/user/repo/releases/tag/v1.0.0-alpha.1',
            'https://github.com/user/repo/releases/tag/v2.0.0-beta.2',
            'https://github.com/user/repo/releases/tag/v1.0.0-rc.1',
        ];

        foreach ($testCases as $url) {
            $version = new Version($url);

            $this->assertSame($url, $version->url);
            $this->assertSame('git', $version->type);
        }
    }

    public function testConstructorWithArchiveUrl(): void
    {
        $url = 'https://github.com/user/repo/archive/refs/tags/v1.0.0.tar.gz';
        $type = 'archive';

        $version = new Version($url, $type);

        $this->assertSame($url, $version->url);
        $this->assertSame($type, $version->type);
    }

    public function testPublicReadonlyProperties(): void
    {
        $url = 'https://gitlab.com/group/project/-/releases/v2.0.0';
        $type = 'git';

        $version = new Version($url, $type);

        // Verify properties are accessible directly
        $this->assertSame($url, $version->url);
        $this->assertSame($type, $version->type);
    }

    public function testDefaultTypeIsGit(): void
    {
        $url = 'https://example.com/any-package/v1.0.0';

        $version = new Version($url);

        $this->assertSame('git', $version->type);
    }

    public function testConstructorWithComplexUrl(): void
    {
        $url = 'https://github.com/user/repo/releases/tag/v1.0.0?utm_source=github&tab=overview';
        $type = 'git';

        $version = new Version($url, $type);

        $this->assertSame($url, $version->url);
        $this->assertSame($type, $version->type);
    }
}
