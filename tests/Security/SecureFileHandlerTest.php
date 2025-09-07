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

namespace PackApi\Tests\Security;

use PackApi\Security\SecureFileHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\ChunkInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

#[CoversClass(SecureFileHandler::class)]
final class SecureFileHandlerTest extends TestCase
{
    public function testValidatePathValid(): void
    {
        $handler = new SecureFileHandler($this->createStub(HttpClientInterface::class));
        $this->assertTrue($handler->validatePath('/path/to/file.txt'));
    }

    public function testValidatePathInvalid(): void
    {
        $handler = new SecureFileHandler($this->createStub(HttpClientInterface::class));
        $this->assertFalse($handler->validatePath('/path/../file.txt'));
    }

    public function testDownloadSafelyValidUrl(): void
    {
        $content = 'hello';
        $client = $this->createStreamClient($content, 'application/zip');
        $handler = new SecureFileHandler($client, sys_get_temp_dir());

        $file = $handler->downloadSafely('https://example.com/file.zip', 10);

        $this->assertFileExists($file);
        $this->assertSame($content, file_get_contents($file));

        unlink($file);
        rmdir(dirname($file));
    }

    public function testDownloadSafelyInvalidUrlThrows(): void
    {
        $handler = new SecureFileHandler($this->createStub(HttpClientInterface::class));

        $this->expectException(\PackApi\Exception\ValidationException::class);
        $handler->downloadSafely('ftp://example.com/file.zip');
    }

    public function testDownloadSafelyDisallowedContentTypeThrows(): void
    {
        $client = $this->createStreamClient('foo', 'image/png');
        $handler = new SecureFileHandler($client, sys_get_temp_dir());

        $this->expectException(\PackApi\Exception\ValidationException::class);
        $this->expectExceptionMessage('Disallowed content type: image/png');

        $handler->downloadSafely('https://example.com/file.png');
    }

    public function testDownloadSafelyExceedsSizeThrows(): void
    {
        $content = str_repeat('A', 11);
        $client = $this->createStreamClient($content, 'application/zip');
        $handler = new SecureFileHandler($client, sys_get_temp_dir());

        $this->expectException(\PackApi\Exception\ValidationException::class);
        $this->expectExceptionMessage('File size exceeds maximum allowed size');

        $handler->downloadSafely('https://example.com/large.zip', 10);
    }

    public function testExtractSafelyTarArchive(): void
    {
        $tmp = sys_get_temp_dir().'/packapi_tar_'.uniqid();
        mkdir($tmp);
        $archive = $tmp.'/test.tar';
        $phar = new \PharData($archive);
        $phar->addFromString('foo.txt', 'bar');

        $dest = $tmp.'/dest';
        $handler = new SecureFileHandler($this->createStub(HttpClientInterface::class));
        $files = $handler->extractSafely($archive, $dest);

        $this->assertSame(['foo.txt'], $files);
        $this->assertFileExists($dest.'/foo.txt');
        $this->assertSame('bar', file_get_contents($dest.'/foo.txt'));

        $this->removeDir($tmp);
    }

    public function testExtractSafelyZipArchive(): void
    {
        $tmp = sys_get_temp_dir().'/packapi_zip_'.uniqid();
        mkdir($tmp);
        $archive = $tmp.'/test.zip';
        $zip = new \ZipArchive();
        $zip->open($archive, \ZipArchive::CREATE);
        $zip->addFromString('bar.txt', 'baz');
        $zip->close();

        $dest = $tmp.'/dest';
        $handler = new SecureFileHandler($this->createStub(HttpClientInterface::class));
        $files = $handler->extractSafely($archive, $dest);

        $this->assertSame(['bar.txt'], $files);
        $this->assertFileExists($dest.'/bar.txt');
        $this->assertSame('baz', file_get_contents($dest.'/bar.txt'));

        $this->removeDir($tmp);
    }

    public function testExtractSafelyUnsupportedFormatThrows(): void
    {
        $tmp = sys_get_temp_dir().'/packapi_bad_'.uniqid();
        mkdir($tmp);
        $archive = $tmp.'/test.rar';
        file_put_contents($archive, 'dummy');

        $handler = new SecureFileHandler($this->createStub(HttpClientInterface::class));

        $this->expectException(\PackApi\Exception\ValidationException::class);
        $this->expectExceptionMessage('Unsupported archive format');

        try {
            $handler->extractSafely($archive, $tmp.'/dest');
        } finally {
            $this->removeDir($tmp);
        }
    }

    public function testExtractSafelyInvalidZipThrows(): void
    {
        $tmp = sys_get_temp_dir().'/packapi_invzip_'.uniqid();
        mkdir($tmp);
        $archive = $tmp.'/test.zip';
        file_put_contents($archive, 'not a zip');

        $handler = new SecureFileHandler($this->createStub(HttpClientInterface::class));

        $this->expectException(\PackApi\Exception\ValidationException::class);
        $this->expectExceptionMessage('Cannot open ZIP archive');

        try {
            $handler->extractSafely($archive, $tmp.'/dest');
        } finally {
            $this->removeDir($tmp);
        }
    }

    private function createStreamClient(string $content, string $contentType): HttpClientInterface
    {
        return new class($content, $contentType) implements HttpClientInterface {
            private ResponseInterface $response;

            public function __construct(private string $content, private string $type)
            {
            }

            public function request(string $method, string $url, array $options = []): ResponseInterface
            {
                return $this->response = new class($this->type) implements ResponseInterface {
                    public function __construct(private string $type)
                    {
                    }

                    public function getStatusCode(): int
                    {
                        return 200;
                    }

                    public function getHeaders(bool $throw = true): array
                    {
                        return ['content-type' => [$this->type]];
                    }

                    public function getContent(bool $throw = true): string
                    {
                        return '';
                    }

                    public function toArray(bool $throw = true): array
                    {
                        return [];
                    }

                    public function cancel(): void
                    {
                    }

                    public function getInfo(?string $type = null): mixed
                    {
                        return null;
                    }
                };
            }

            public function stream(ResponseInterface|iterable $responses, ?float $timeout = null): ResponseStreamInterface
            {
                $response = $this->response;
                $content = $this->content;

                return new class($response, $content) implements ResponseStreamInterface {
                    private bool $sent = false;

                    public function __construct(private ResponseInterface $response, private string $content)
                    {
                    }

                    public function key(): ResponseInterface
                    {
                        return $this->response;
                    }

                    public function current(): ChunkInterface
                    {
                        return new class($this->content) implements ChunkInterface {
                            public function __construct(private string $data)
                            {
                            }

                            public function isTimeout(): bool
                            {
                                return false;
                            }

                            public function isFirst(): bool
                            {
                                return false;
                            }

                            public function isLast(): bool
                            {
                                return true;
                            }

                            public function getInformationalStatus(): ?array
                            {
                                return null;
                            }

                            public function getContent(): string
                            {
                                return $this->data;
                            }

                            public function getOffset(): int
                            {
                                return 0;
                            }

                            public function getError(): ?string
                            {
                                return null;
                            }
                        };
                    }

                    public function next(): void
                    {
                        $this->sent = true;
                    }

                    public function valid(): bool
                    {
                        return !$this->sent;
                    }

                    public function rewind(): void
                    {
                        $this->sent = false;
                    }
                };
            }

            public function withOptions(array $options): static
            {
                return $this;
            }
        };
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileInfo) {
            if ($fileInfo->isDir()) {
                rmdir($fileInfo->getRealPath());
            } else {
                unlink($fileInfo->getRealPath());
            }
        }
        rmdir($dir);
    }
}
