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

use PackApi\Model\File;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(File::class)]
final class FileTest extends TestCase
{
    public function testConstructorAndGettersWithAllValues(): void
    {
        $path = 'src/Models/User.php';
        $size = 2048;
        $isDirectory = false;
        $timestamp = new \DateTimeImmutable('2023-06-15 14:30:25');

        $file = new File($path, $size, $isDirectory, $timestamp);

        $this->assertSame($path, $file->getPath());
        $this->assertSame($size, $file->getSize());
        $this->assertSame($isDirectory, $file->isDirectory());
        $this->assertSame($timestamp, $file->getTimestamp());

        // Test public readonly properties
        $this->assertSame($path, $file->path);
        $this->assertSame($size, $file->size);
        $this->assertSame($isDirectory, $file->isDirectory);
        $this->assertSame($timestamp, $file->timestamp);
    }

    public function testConstructorWithMinimalValues(): void
    {
        $path = 'README.md';
        $size = 1024;

        $file = new File($path, $size);

        $this->assertSame($path, $file->getPath());
        $this->assertSame($size, $file->getSize());
        $this->assertFalse($file->isDirectory());
        $this->assertNull($file->getTimestamp());
    }

    public function testConstructorWithDirectory(): void
    {
        $path = 'tests/';
        $size = 0;
        $isDirectory = true;

        $file = new File($path, $size, $isDirectory);

        $this->assertSame($path, $file->getPath());
        $this->assertSame($size, $file->getSize());
        $this->assertTrue($file->isDirectory());
        $this->assertNull($file->getTimestamp());
    }

    public function testGetHumanSizeWithBytes(): void
    {
        $file = new File('small.txt', 512);

        $this->assertSame('512 B', $file->getHumanSize());
        $this->assertSame('512 B', $file->getHumanSize(0));
        $this->assertSame('512 B', $file->getHumanSize(2));
    }

    public function testGetHumanSizeWithKilobytes(): void
    {
        $file = new File('medium.txt', 1536); // 1.5 KB

        $this->assertSame('1.5 KB', $file->getHumanSize());
        $this->assertSame('1.5 KB', $file->getHumanSize(2));
        $this->assertSame('2 KB', $file->getHumanSize(0));
    }

    public function testGetHumanSizeWithMegabytes(): void
    {
        $file = new File('large.txt', 2621440); // 2.5 MB

        $this->assertSame('2.5 MB', $file->getHumanSize());
        $this->assertSame('2.5 MB', $file->getHumanSize(2));
        $this->assertSame('3 MB', $file->getHumanSize(0));
    }

    public function testGetHumanSizeWithGigabytes(): void
    {
        $file = new File('huge.txt', 1610612736); // 1.5 GB

        $this->assertSame('1.5 GB', $file->getHumanSize());
        $this->assertSame('1.5 GB', $file->getHumanSize(2));
        $this->assertSame('2 GB', $file->getHumanSize(0));
    }

    public function testGetHumanSizeWithTerabytes(): void
    {
        $file = new File('massive.txt', 1649267441664); // 1.5 TB

        $this->assertSame('1.5 TB', $file->getHumanSize());
        $this->assertSame('1.5 TB', $file->getHumanSize(2));
        $this->assertSame('2 TB', $file->getHumanSize(0));
    }

    public function testGetHumanSizeWithZeroSize(): void
    {
        $file = new File('empty.txt', 0);

        $this->assertSame('0 B', $file->getHumanSize());
        $this->assertSame('0 B', $file->getHumanSize(2));
    }

    public function testGetHumanSizeWithExactKilobyte(): void
    {
        $file = new File('exact.txt', 1024); // exactly 1 KB

        $this->assertSame('1 KB', $file->getHumanSize());
        $this->assertSame('1 KB', $file->getHumanSize(2));
    }

    public function testGetHumanSizeWithExactMegabyte(): void
    {
        $file = new File('exact.txt', 1048576); // exactly 1 MB

        $this->assertSame('1 MB', $file->getHumanSize());
        $this->assertSame('1 MB', $file->getHumanSize(2));
    }

    public function testGetHumanSizeWithCustomDecimals(): void
    {
        $file = new File('test.txt', 1536); // 1.5 KB

        $this->assertSame('1.5 KB', $file->getHumanSize(1));
        $this->assertSame('1.5 KB', $file->getHumanSize(2));
        $this->assertSame('1.5 KB', $file->getHumanSize(3));
    }

    public function testGetHumanSizeWithLargeDecimals(): void
    {
        $file = new File('test.txt', 1536); // 1.5 KB

        $this->assertSame('1.5 KB', $file->getHumanSize(6));
    }

    public function testDirectoryWithZeroSize(): void
    {
        $file = new File('src/', 0, true);

        $this->assertTrue($file->isDirectory());
        $this->assertSame(0, $file->getSize());
        $this->assertSame('0 B', $file->getHumanSize());
    }

    public function testFileWithVeryLargeSize(): void
    {
        // Size larger than TB
        $file = new File('enormous.txt', 1125899906842624); // 1 PB (but we only support up to TB)

        // Should still show as TB since that's the largest unit we support
        $this->assertStringContainsString('TB', $file->getHumanSize());
    }
}
