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
use PackApi\Bridge\GitHub\GitHubMetadataProvider;
use PackApi\Model\Metadata;
use PackApi\Package\ComposerPackage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(GitHubMetadataProvider::class)]
final class GitHubMetadataProviderTest extends TestCase
{
    public function testGetMetadataReturnsModel(): void
    {
        $responses = [
            new MockResponse(json_encode([
                'name' => 'repo',
                'description' => 'desc',
                'license' => ['name' => 'MIT'],
                'html_url' => 'https://github.com/owner/repo',
            ])),
        ];
        $client = new GitHubApiClient(new MockHttpClient($responses));

        $provider = new GitHubMetadataProvider($client);
        $package = new ComposerPackage('owner/repo');
        $package->setRepositoryUrl('https://github.com/owner/repo');

        $metadata = $provider->getMetadata($package);

        $this->assertInstanceOf(Metadata::class, $metadata);
        $this->assertSame('repo', $metadata->name);
        $this->assertSame('desc', $metadata->description);
        $this->assertSame('MIT', $metadata->license);
        $this->assertSame('https://github.com/owner/repo', $metadata->repository);
    }
}
