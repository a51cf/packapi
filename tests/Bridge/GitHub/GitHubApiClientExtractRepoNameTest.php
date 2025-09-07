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

namespace PackApi\Tests\Bridge\GitHub;

use PackApi\Bridge\GitHub\GitHubApiClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

#[CoversClass(GitHubApiClient::class)]
final class GitHubApiClientExtractRepoNameTest extends TestCase
{
    private function getStubClient(array $responses = []): HttpClientInterface
    {
        return new class($responses) implements HttpClientInterface {
            public function __construct(private array $responses)
            {
            }

            public function request(string $method, string $url, array $options = []): ResponseInterface
            {
                $key = $method.' '.$url;
                [$status, $data] = $this->responses[$key] ?? [200, []];
                $content = json_encode($data);

                return new class($status, $content) implements ResponseInterface {
                    public function __construct(private int $status, private string $content)
                    {
                    }

                    public function getStatusCode(): int
                    {
                        return $this->status;
                    }

                    public function getHeaders(bool $throw = true): array
                    {
                        return [];
                    }

                    public function getContent(bool $throw = true): string
                    {
                        return $this->content;
                    }

                    public function toArray(bool $throw = true): array
                    {
                        return json_decode($this->content, true);
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
                throw new \LogicException('Not implemented');
            }

            public function withOptions(array $options): static
            {
                return $this;
            }
        };
    }

    public function testExtractRepoNameFromVariousUrls(): void
    {
        $client = new GitHubApiClient($this->getStubClient());

        $this->assertSame('symfony/symfony', $client->extractRepoName('https://github.com/symfony/symfony'));
        $this->assertSame('symfony/symfony', $client->extractRepoName('git@github.com:symfony/symfony.git'));
        $this->assertSame('symfony/symfony', $client->extractRepoName('symfony/symfony'));
        $this->assertNull($client->extractRepoName('https://example.com/foo'));
    }

    public function testFetchFileContentDecodesBase64(): void
    {
        $responses = [
            'GET /repos/owner/repo/contents/file.txt' => [200, ['content' => base64_encode('hello')]],
            'GET /repos/owner/repo/contents/missing.txt' => [404, ['message' => 'Not Found']],
        ];
        $client = new GitHubApiClient($this->getStubClient($responses));

        $this->assertSame('hello', $client->fetchFileContent('owner/repo', 'file.txt'));
        $this->assertNull($client->fetchFileContent('owner/repo', 'missing.txt'));
    }
}
