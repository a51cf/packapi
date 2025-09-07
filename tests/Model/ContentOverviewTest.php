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

use PackApi\Model\ContentOverview;
use PackApi\Model\File;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContentOverview::class)]
final class ContentOverviewTest extends TestCase
{
    public function testConstructorAndGettersWithAllValues(): void
    {
        $fileCount = 25;
        $totalSize = 2048576;
        $hasReadme = true;
        $hasLicense = true;
        $hasTests = true;
        $ignoredFiles = ['.gitignore', 'node_modules/', '.env'];
        $files = [
            new File('README.md', 1024),
            new File('package.json', 512),
            new File('src/', 0, true),
        ];
        $hasGitattributes = true;
        $hasGitignore = true;

        $overview = new ContentOverview(
            $fileCount,
            $totalSize,
            $hasReadme,
            $hasLicense,
            $hasTests,
            $ignoredFiles,
            $files,
            $hasGitattributes,
            $hasGitignore
        );

        $this->assertSame($fileCount, $overview->getFileCount());
        $this->assertSame($totalSize, $overview->getTotalSize());
        $this->assertTrue($overview->hasReadme());
        $this->assertTrue($overview->hasLicense());
        $this->assertTrue($overview->hasTests());
        $this->assertSame($ignoredFiles, $overview->getIgnoredFiles());
        $this->assertSame($files, $overview->getFiles());
        $this->assertTrue($overview->hasGitattributes());
        $this->assertTrue($overview->hasGitignore());

        // Test public readonly properties
        $this->assertSame($fileCount, $overview->fileCount);
        $this->assertSame($totalSize, $overview->totalSize);
        $this->assertTrue($overview->hasReadme);
        $this->assertTrue($overview->hasLicense);
        $this->assertTrue($overview->hasTests);
        $this->assertSame($ignoredFiles, $overview->ignoredFiles);
        $this->assertSame($files, $overview->files);
        $this->assertTrue($overview->hasGitattributes);
        $this->assertTrue($overview->hasGitignore);
    }

    public function testConstructorWithMinimalValues(): void
    {
        $fileCount = 5;
        $totalSize = 1024;

        $overview = new ContentOverview($fileCount, $totalSize);

        $this->assertSame($fileCount, $overview->getFileCount());
        $this->assertSame($totalSize, $overview->getTotalSize());
        $this->assertFalse($overview->hasReadme());
        $this->assertFalse($overview->hasLicense());
        $this->assertFalse($overview->hasTests());
        $this->assertSame([], $overview->getIgnoredFiles());
        $this->assertSame([], $overview->getFiles());
        $this->assertFalse($overview->hasGitattributes());
        $this->assertFalse($overview->hasGitignore());
    }

    public function testConstructorWithSomeFlags(): void
    {
        $fileCount = 10;
        $totalSize = 4096;
        $hasReadme = true;
        $hasTests = true;

        $overview = new ContentOverview($fileCount, $totalSize, $hasReadme, false, $hasTests);

        $this->assertSame($fileCount, $overview->getFileCount());
        $this->assertSame($totalSize, $overview->getTotalSize());
        $this->assertTrue($overview->hasReadme());
        $this->assertFalse($overview->hasLicense());
        $this->assertTrue($overview->hasTests());
        $this->assertSame([], $overview->getIgnoredFiles());
        $this->assertSame([], $overview->getFiles());
        $this->assertFalse($overview->hasGitattributes());
        $this->assertFalse($overview->hasGitignore());
    }

    public function testConstructorWithIgnoredFilesOnly(): void
    {
        $fileCount = 3;
        $totalSize = 512;
        $ignoredFiles = ['dist/', 'coverage/', '.env.local'];

        $overview = new ContentOverview($fileCount, $totalSize, false, false, false, $ignoredFiles);

        $this->assertSame($fileCount, $overview->getFileCount());
        $this->assertSame($totalSize, $overview->getTotalSize());
        $this->assertFalse($overview->hasReadme());
        $this->assertFalse($overview->hasLicense());
        $this->assertFalse($overview->hasTests());
        $this->assertSame($ignoredFiles, $overview->getIgnoredFiles());
        $this->assertSame([], $overview->getFiles());
        $this->assertFalse($overview->hasGitattributes());
        $this->assertFalse($overview->hasGitignore());
    }

    public function testConstructorWithFilesArray(): void
    {
        $fileCount = 2;
        $totalSize = 1536;
        $files = [
            new File('index.js', 1024, false, new \DateTimeImmutable('2023-06-15')),
            new File('tests/', 512, true),
        ];

        $overview = new ContentOverview($fileCount, $totalSize, false, false, false, [], $files);

        $this->assertSame($fileCount, $overview->getFileCount());
        $this->assertSame($totalSize, $overview->getTotalSize());
        $this->assertCount(2, $overview->getFiles());
        $this->assertSame($files, $overview->getFiles());
        $this->assertInstanceOf(File::class, $overview->getFiles()[0]);
        $this->assertInstanceOf(File::class, $overview->getFiles()[1]);
    }

    public function testConstructorWithZeroValues(): void
    {
        $overview = new ContentOverview(0, 0);

        $this->assertSame(0, $overview->getFileCount());
        $this->assertSame(0, $overview->getTotalSize());
        $this->assertFalse($overview->hasReadme());
        $this->assertFalse($overview->hasLicense());
        $this->assertFalse($overview->hasTests());
        $this->assertSame([], $overview->getIgnoredFiles());
        $this->assertSame([], $overview->getFiles());
        $this->assertFalse($overview->hasGitattributes());
        $this->assertFalse($overview->hasGitignore());
    }
}
