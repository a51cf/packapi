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
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(GitHubApiClient::class)]
final class GitHubApiClientTest extends TestCase
{
    private function getMockClient(array $responses, array &$calls): MockHttpClient
    {
        return new MockHttpClient(function (string $method, string $url, array $options = []) use (&$calls, $responses) {
            $path = (string) parse_url($url, PHP_URL_PATH);
            $calls[] = [$method, $path, $options];
            $key = $method.' '.$path;
            [$status, $data] = $responses[$key] ?? [200, []];

            return new MockResponse(json_encode($data), ['http_code' => $status]);
        });
    }

    public function testFetchRepoMetadataMakesCorrectRequest(): void
    {
        $responses = [
            'GET /repos/owner/repo' => [200, ['name' => 'repo']],
        ];
        $calls = [];
        $client = new GitHubApiClient($this->getMockClient($responses, $calls));

        $data = $client->fetchRepoMetadata('owner/repo');

        $this->assertCount(1, $calls);
        $this->assertSame('GET', $calls[0][0]);
        $this->assertSame('/repos/owner/repo', $calls[0][1]);
        $this->assertSame(['name' => 'repo'], $data);
    }

    public function testSearchRepositoriesUsesQueryParameters(): void
    {
        $responses = [
            'GET /search/repositories' => [200, ['items' => []]],
        ];
        $calls = [];
        $client = new GitHubApiClient($this->getMockClient($responses, $calls));

        $client->searchRepositories('symfony', 5, 'stars', 'desc');

        $this->assertCount(1, $calls);
        [$method, $url, $options] = $calls[0];
        $this->assertSame('GET', $method);
        $this->assertSame('/search/repositories', $url);
        $this->assertSame(
            ['q' => 'symfony', 'per_page' => 5, 'sort' => 'stars', 'order' => 'desc'],
            $options['query'] ?? []
        );
    }

    public function testFetchSecurityAdvisoriesAggregatesData(): void
    {
        $responses = [
            'GET /repos/owner/repo/security-advisories' => [200, [['id' => 1]]],
            'GET /repos/owner/repo/vulnerability-alerts' => [403, []],
            'GET /repos/owner/repo/contents/SECURITY.md' => [404, []],
        ];
        $calls = [];
        $client = new GitHubApiClient($this->getMockClient($responses, $calls));

        $data = $client->fetchSecurityAdvisories('owner/repo');

        $this->assertCount(3, $calls);
        $this->assertSame(1, $data['advisory_count']);
        $this->assertFalse($data['has_security_policy']);
    }
}
