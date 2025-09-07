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

use PackApi\Bridge\GitHub\GitHubActivityProvider;
use PackApi\Bridge\GitHub\GitHubContentProvider;
use PackApi\Bridge\GitHub\GitHubMetadataProvider;
use PackApi\Bridge\GitHub\GitHubProviderFactory;
use PackApi\Bridge\GitHub\GitHubSearchProvider;
use PackApi\Bridge\GitHub\GitHubSecurityProvider;
use PackApi\Bridge\GitHub\GitHubStatisticProvider;
use PackApi\Http\HttpClientFactoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(GitHubProviderFactory::class)]
final class GitHubProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesProvidersAndUsesToken(): void
    {
        $httpFactory = $this->createMock(HttpClientFactoryInterface::class);
        $client = $this->createMock(HttpClientInterface::class);
        $httpFactory->expects($this->once())->method('createClient')->willReturn($client);
        $client->expects($this->once())
            ->method('withOptions')
            ->with($this->callback(function (array $options) {
                return 'https://api.github.com/' === $options['base_uri']
                    && 'Bearer my-token' === $options['headers']['Authorization'];
            }))
            ->willReturnSelf();

        $factory = new GitHubProviderFactory($httpFactory, 'my-token');

        $this->assertInstanceOf(GitHubSearchProvider::class, $factory->createSearchProvider());
        $this->assertInstanceOf(GitHubMetadataProvider::class, $factory->createMetadataProvider());
        $this->assertInstanceOf(GitHubContentProvider::class, $factory->createContentProvider());
        $this->assertInstanceOf(GitHubActivityProvider::class, $factory->createActivityProvider());
        $this->assertInstanceOf(GitHubSecurityProvider::class, $factory->createSecurityProvider());
        $this->assertInstanceOf(GitHubStatisticProvider::class, $factory->createStatisticProvider());
    }
}
