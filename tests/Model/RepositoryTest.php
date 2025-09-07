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

use PackApi\Model\Repository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Repository::class)]
final class RepositoryTest extends TestCase
{
    public function testConstructorWithAllValues(): void
    {
        $url = 'https://github.com/symfony/symfony';
        $type = 'git';

        $repository = new Repository($url, $type);

        $this->assertSame($url, $repository->url);
        $this->assertSame($type, $repository->type);
    }

    public function testConstructorWithUrlOnly(): void
    {
        $url = 'https://github.com/laravel/laravel';

        $repository = new Repository($url);

        $this->assertSame($url, $repository->url);
        $this->assertSame('git', $repository->type); // default value
    }

    public function testConstructorWithDifferentTypes(): void
    {
        $testCases = [
            ['url' => 'https://github.com/user/repo', 'type' => 'git'],
            ['url' => 'https://bitbucket.org/user/repo', 'type' => 'hg'],
            ['url' => 'https://svn.example.com/repo', 'type' => 'svn'],
            ['url' => 'https://gitlab.com/user/repo', 'type' => 'git'],
        ];

        foreach ($testCases as $testCase) {
            $repository = new Repository($testCase['url'], $testCase['type']);

            $this->assertSame($testCase['url'], $repository->url);
            $this->assertSame($testCase['type'], $repository->type);
        }
    }

    public function testConstructorWithSshUrl(): void
    {
        $url = 'git@github.com:user/repo.git';
        $type = 'git';

        $repository = new Repository($url, $type);

        $this->assertSame($url, $repository->url);
        $this->assertSame($type, $repository->type);
    }

    public function testConstructorWithNullType(): void
    {
        $url = 'https://example.com/repo';

        $repository = new Repository($url, null);

        $this->assertSame($url, $repository->url);
        $this->assertNull($repository->type);
    }

    public function testConstructorWithEmptyStringType(): void
    {
        $url = 'https://example.com/repo';

        $repository = new Repository($url, '');

        $this->assertSame($url, $repository->url);
        $this->assertSame('', $repository->type);
    }

    public function testConstructorWithComplexUrl(): void
    {
        $url = 'https://github.com/user/repo.git?ref=main&depth=1';
        $type = 'git';

        $repository = new Repository($url, $type);

        $this->assertSame($url, $repository->url);
        $this->assertSame($type, $repository->type);
    }

    public function testPublicReadonlyProperties(): void
    {
        $url = 'https://gitlab.com/group/project';
        $type = 'git';

        $repository = new Repository($url, $type);

        // Verify properties are accessible directly
        $this->assertSame($url, $repository->url);
        $this->assertSame($type, $repository->type);
    }

    public function testDefaultTypeIsGit(): void
    {
        $url = 'https://example.com/any-repo';

        $repository = new Repository($url);

        $this->assertSame('git', $repository->type);
    }
}
