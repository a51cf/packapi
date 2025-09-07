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
use PackApi\Bridge\GitHub\GitHubContentProvider;
use PackApi\Model\ContentOverview;
use PackApi\Package\ComposerPackage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(GitHubContentProvider::class)]
final class GitHubContentProviderTest extends TestCase
{
    public function testGetContentOverviewBuildsModel(): void
    {
        $responses = [
            new MockResponse(json_encode(['default_branch' => 'main'])), // fetchRepoMetadata
            new MockResponse(json_encode([
                ['name' => 'README.md', 'size' => 10, 'type' => 'file'],
                ['name' => '.gitignore', 'size' => 0, 'type' => 'file'],
                ['name' => 'src', 'size' => 0, 'type' => 'dir'],
            ])),
            new MockResponse(json_encode([])), // README
            new MockResponse(json_encode([])), // LICENSE
            new MockResponse('', ['http_code' => 404]), // SECURITY.md
            new MockResponse('', ['http_code' => 404]), // composer.json
            new MockResponse('', ['http_code' => 404]), // package.json
        ];
        $client = new GitHubApiClient(new MockHttpClient($responses));

        $provider = new GitHubContentProvider($client);
        $package = new ComposerPackage('owner/repo');
        $package->setRepositoryUrl('https://github.com/owner/repo');

        $overview = $provider->getContentOverview($package);

        $this->assertInstanceOf(ContentOverview::class, $overview);
        $this->assertSame(3, $overview->getFileCount());
        $this->assertTrue($overview->hasReadme());
        $this->assertTrue($overview->hasLicense());
        $this->assertTrue($overview->hasGitignore());
    }
}
