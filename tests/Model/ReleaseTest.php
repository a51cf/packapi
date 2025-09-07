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

use PackApi\Model\Release;
use PackApi\Model\Repository;
use PackApi\Model\Version;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Release::class)]
final class ReleaseTest extends TestCase
{
    public function testConstructorWithAllValues(): void
    {
        $version = new Version('https://github.com/symfony/symfony/releases/tag/v6.3.0');
        $repository = new Repository('https://github.com/symfony/symfony');
        $createdAt = new \DateTimeImmutable('2023-06-15 14:30:00');

        $release = new Release($version, $repository, $createdAt);

        $this->assertSame($version, $release->version);
        $this->assertSame($repository, $release->repository);
        $this->assertSame($createdAt, $release->createdAt);
    }

    public function testConstructorWithMinimalValues(): void
    {
        $version = new Version('https://github.com/laravel/laravel/releases/tag/v10.0.0');
        $repository = new Repository('https://github.com/laravel/laravel');

        $release = new Release($version, $repository);

        $this->assertSame($version, $release->version);
        $this->assertSame($repository, $release->repository);
        $this->assertNull($release->createdAt);
    }

    public function testConstructorWithNullCreatedAt(): void
    {
        $version = new Version('https://github.com/facebook/react/releases/tag/v18.2.0');
        $repository = new Repository('https://github.com/facebook/react');

        $release = new Release($version, $repository, null);

        $this->assertSame($version, $release->version);
        $this->assertSame($repository, $release->repository);
        $this->assertNull($release->createdAt);
    }

    public function testConstructorWithDifferentVersionTypes(): void
    {
        $testCases = [
            [
                'version' => new Version('https://github.com/user/repo/releases/tag/v1.0.0', 'git'),
                'repository' => new Repository('https://github.com/user/repo', 'git'),
            ],
            [
                'version' => new Version('https://registry.npmjs.org/package/-/package-2.0.0.tgz', 'npm'),
                'repository' => new Repository('https://github.com/user/package', 'git'),
            ],
            [
                'version' => new Version('https://packagist.org/packages/vendor/package#3.0.0', 'composer'),
                'repository' => new Repository('https://github.com/vendor/package', 'git'),
            ],
        ];

        foreach ($testCases as $testCase) {
            $createdAt = new \DateTimeImmutable('2023-01-01');
            $release = new Release($testCase['version'], $testCase['repository'], $createdAt);

            $this->assertSame($testCase['version'], $release->version);
            $this->assertSame($testCase['repository'], $release->repository);
            $this->assertSame($createdAt, $release->createdAt);
            $this->assertInstanceOf(Version::class, $release->version);
            $this->assertInstanceOf(Repository::class, $release->repository);
        }
    }

    public function testConstructorWithDifferentRepositoryTypes(): void
    {
        $version = new Version('https://example.com/v1.0.0');

        $testCases = [
            new Repository('https://github.com/user/repo', 'git'),
            new Repository('https://bitbucket.org/user/repo', 'hg'),
            new Repository('https://gitlab.com/user/repo', 'git'),
            new Repository('git@github.com:user/repo.git', 'git'),
        ];

        foreach ($testCases as $repository) {
            $release = new Release($version, $repository);

            $this->assertSame($version, $release->version);
            $this->assertSame($repository, $release->repository);
            $this->assertInstanceOf(Repository::class, $release->repository);
        }
    }

    public function testConstructorWithVariousCreatedAtDates(): void
    {
        $version = new Version('https://github.com/user/repo/releases/tag/v1.0.0');
        $repository = new Repository('https://github.com/user/repo');

        $testDates = [
            new \DateTimeImmutable('2023-01-01 00:00:00'),
            new \DateTimeImmutable('2023-06-15 14:30:25'),
            new \DateTimeImmutable('2023-12-31 23:59:59'),
            new \DateTimeImmutable('2020-02-29 12:00:00'), // leap year
            new \DateTimeImmutable('now'),
        ];

        foreach ($testDates as $createdAt) {
            $release = new Release($version, $repository, $createdAt);

            $this->assertSame($version, $release->version);
            $this->assertSame($repository, $release->repository);
            $this->assertSame($createdAt, $release->createdAt);
            $this->assertInstanceOf(\DateTimeImmutable::class, $release->createdAt);
        }
    }

    public function testPublicReadonlyProperties(): void
    {
        $version = new Version('https://github.com/symfony/console/releases/tag/v6.3.0');
        $repository = new Repository('https://github.com/symfony/console');
        $createdAt = new \DateTimeImmutable('2023-06-15');

        $release = new Release($version, $repository, $createdAt);

        // Verify all properties are accessible directly
        $this->assertInstanceOf(Version::class, $release->version);
        $this->assertInstanceOf(Repository::class, $release->repository);
        $this->assertInstanceOf(\DateTimeImmutable::class, $release->createdAt);

        // Verify they contain the expected values
        $this->assertSame($version, $release->version);
        $this->assertSame($repository, $release->repository);
        $this->assertSame($createdAt, $release->createdAt);
    }

    public function testConstructorWithComplexObjects(): void
    {
        // Create complex version and repository objects
        $version = new Version('https://github.com/complex/package/releases/tag/v2.5.0-beta.1', 'git');
        $repository = new Repository('git@github.com:complex/package.git', 'git');
        $createdAt = new \DateTimeImmutable('2023-06-15T14:30:25+02:00');

        $release = new Release($version, $repository, $createdAt);

        $this->assertSame('https://github.com/complex/package/releases/tag/v2.5.0-beta.1', $release->version->url);
        $this->assertSame('git', $release->version->type);
        $this->assertSame('git@github.com:complex/package.git', $release->repository->url);
        $this->assertSame('git', $release->repository->type);
        $this->assertSame($createdAt, $release->createdAt);
    }
}
