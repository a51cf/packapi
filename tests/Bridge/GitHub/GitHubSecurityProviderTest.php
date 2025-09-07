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
use PackApi\Bridge\GitHub\GitHubSecurityProvider;
use PackApi\Model\SecurityAdvisory;
use PackApi\Package\ComposerPackage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(GitHubSecurityProvider::class)]
final class GitHubSecurityProviderTest extends TestCase
{
    public function testSupportsChecksRepository(): void
    {
        $client = new GitHubApiClient(new MockHttpClient());
        $provider = new GitHubSecurityProvider($client);

        $pkg = new ComposerPackage('owner/repo');
        $pkg->setRepositoryUrl('https://github.com/owner/repo');
        $this->assertTrue($provider->supports($pkg));

        $pkg2 = new ComposerPackage('foo/bar');
        $pkg2->setRepositoryUrl('https://gitlab.com/foo/bar');
        $this->assertFalse($provider->supports($pkg2));
    }

    public function testGetSecurityAdvisoriesReturnsObjects(): void
    {
        $responses = [
            new MockResponse(json_encode([
                ['ghsa_id' => 'GHSA-1', 'summary' => 'A', 'severity' => 'high', 'html_url' => 'link'],
            ])),
            new MockResponse('', ['http_code' => 404]),
            new MockResponse('', ['http_code' => 404]),
        ];
        $client = new GitHubApiClient(new MockHttpClient($responses));
        $provider = new GitHubSecurityProvider($client);
        $pkg = new ComposerPackage('owner/repo');
        $pkg->setRepositoryUrl('https://github.com/owner/repo');

        $result = $provider->getSecurityAdvisories($pkg);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(SecurityAdvisory::class, $result[0]);
        $this->assertSame('GHSA-1', $result[0]->getId());
    }
}
