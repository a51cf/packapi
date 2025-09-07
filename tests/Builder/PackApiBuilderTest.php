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

namespace PackApi\Bridge\GitHub;

if (!interface_exists(ProviderFactoryInterface::class, false)) {
    interface ProviderFactoryInterface
    {
    }
}

namespace Symfony\Component\HttpClient;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

if (!class_exists(CachingHttpClient::class, false)) {
    class CachingHttpClient implements HttpClientInterface
    {
        private array $options;
        public function __construct(private HttpClientInterface $client, private $store = null, array $options = [])
        {
            $this->options = $options;
        }

        public function request(string $method, string $url, array $options = []): ResponseInterface
        {
            return $this->client->request($method, $url, $options);
        }

        public function stream($responses, ?float $timeout = null): ResponseStreamInterface
        {
            return $this->client->stream($responses, $timeout);
        }

        public function withOptions(array $options): static
        {
            return new static($this->client->withOptions($options), $this->store, $options);
        }
    }
}

namespace Symfony\Component\HttpKernel\HttpCache;

if (!interface_exists(StoreInterface::class, false)) {
    interface StoreInterface
    {
    }
}

namespace PackApi\Http\Middleware;

namespace PackApi\Tests\Builder;

use PackApi\Bridge\GitHub\GitHubActivityProvider;
use PackApi\Bridge\GitHub\GitHubContentProvider;
use PackApi\Bridge\GitHub\GitHubMetadataProvider;
use PackApi\Bridge\GitHub\GitHubSecurityProvider;
use PackApi\Bridge\GitHub\GitHubStatisticProvider;
use PackApi\Bridge\JsDelivr\JsDelivrContentProvider;
use PackApi\Bridge\JsDelivr\JsDelivrMetadataProvider;
use PackApi\Bridge\JsDelivr\JsDelivrStatsProvider;
use PackApi\Bridge\OSV\OSVSecurityProvider;
use PackApi\Bridge\Packagist\PackagistActivityProvider;
use PackApi\Bridge\Packagist\PackagistContentProvider;
use PackApi\Bridge\Packagist\PackagistMetadataProvider;
use PackApi\Bridge\Packagist\PackagistSecurityProvider;
use PackApi\Builder\PackApiBuilder;
use PackApi\Config\Configuration;
use PackApi\Http\HttpClientFactoryInterface;
use PackApi\Inspector\ActivityInspector;
use PackApi\Inspector\ContentInspector;
use PackApi\Inspector\DownloadStatsInspector;
use PackApi\Inspector\MetadataInspector;
use PackApi\Inspector\PackageInspectorFacade;
use PackApi\Inspector\QualityInspector;
use PackApi\Inspector\SecurityInspector;
use PackApi\System\Composer\ComposerDownloadStatsProvider;
use PackApi\System\Npm\NpmContentProvider;
use PackApi\System\Npm\NpmDownloadStatsProvider;
use PackApi\System\Npm\NpmMetadataProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;

#[CoversClass(PackApiBuilder::class)]
final class PackApiBuilderTest extends TestCase
{
    private function getInspectorProviders(object $inspector): array
    {
        $ref = new \ReflectionObject($inspector);
        $prop = $ref->getProperty('providers');
        $prop->setAccessible(true);

        return array_values(iterator_to_array($prop->getValue($inspector)));
    }

    private function assertProvidersInstances(array $providers, array $expected): void
    {
        foreach ($providers as $provider) {
            $this->assertContains(get_class($provider), $expected);
        }
    }

    public function testBuildCreatesFacadeWithCorrectProviders(): void
    {
        $client = new MockHttpClient();
        $factory = $this->createMock(HttpClientFactoryInterface::class);
        $factory->method('createClient')->willReturn($client);

        $builder = new PackApiBuilder(new Configuration(), $factory);
        $facade = $builder->build();

        $this->assertInstanceOf(PackageInspectorFacade::class, $facade);
        $this->assertInstanceOf(MetadataInspector::class, $facade->metadataInspector);
        $this->assertInstanceOf(DownloadStatsInspector::class, $facade->downloadStatsInspector);
        $this->assertInstanceOf(ContentInspector::class, $facade->contentInspector);
        $this->assertInstanceOf(ActivityInspector::class, $facade->activityInspector);
        $this->assertInstanceOf(SecurityInspector::class, $facade->securityInspector);
        $this->assertInstanceOf(QualityInspector::class, $facade->qualityInspector);

        $metadataProviders = $this->getInspectorProviders($facade->metadataInspector);
        $this->assertCount(4, $metadataProviders);
        $this->assertProvidersInstances($metadataProviders, [
            PackagistMetadataProvider::class,
            NpmMetadataProvider::class,
            GitHubMetadataProvider::class,
            JsDelivrMetadataProvider::class,
        ]);

        $statsProviders = $this->getInspectorProviders($facade->downloadStatsInspector);
        $this->assertCount(4, $statsProviders);
        $this->assertProvidersInstances($statsProviders, [
            ComposerDownloadStatsProvider::class,
            NpmDownloadStatsProvider::class,
            JsDelivrStatsProvider::class,
            GitHubStatisticProvider::class,
        ]);

        $contentProviders = $this->getInspectorProviders($facade->contentInspector);
        $this->assertCount(4, $contentProviders);
        $this->assertProvidersInstances($contentProviders, [
            PackagistContentProvider::class,
            NpmContentProvider::class,
            JsDelivrContentProvider::class,
            GitHubContentProvider::class,
        ]);

        $activityProviders = $this->getInspectorProviders($facade->activityInspector);
        $this->assertCount(2, $activityProviders);
        $this->assertProvidersInstances($activityProviders, [
            GitHubActivityProvider::class,
            PackagistActivityProvider::class,
        ]);

        $securityProviders = $this->getInspectorProviders($facade->securityInspector);
        $this->assertCount(3, $securityProviders);
        $this->assertProvidersInstances($securityProviders, [
            OSVSecurityProvider::class,
            GitHubSecurityProvider::class,
            PackagistSecurityProvider::class,
        ]);
    }

    public function testBuilderHandlesTokenCacheAndLogger(): void
    {
        $cache = $this->createMock(CacheItemPoolInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $builder = new PackApiBuilder(new Configuration());
        $builder
            ->withGitHubToken('token123')
            ->useCache($cache)
            ->withLogger($logger);

        $facade = $builder->build();

        $providers = $this->getInspectorProviders($facade->metadataInspector);
        foreach ($providers as $provider) {
            if ($provider instanceof GitHubMetadataProvider) {
                $apiClientRef = new \ReflectionObject($provider);
                $clientProp = $apiClientRef->getProperty('client');
                $clientProp->setAccessible(true);
                $apiClient = $clientProp->getValue($provider);

                $httpRef = new \ReflectionObject($apiClient);
                $httpProp = $httpRef->getProperty('httpClient');
                $httpProp->setAccessible(true);
                $http = $httpProp->getValue($apiClient);

                // Unwrap decorators to reach the underlying client
                $inner = $http;
                while ($inner instanceof \PackApi\Http\Middleware\LoggingMiddleware || $inner instanceof \Symfony\Component\HttpClient\CachingHttpClient) {
                    $innerRef = new \ReflectionObject($inner);
                    $clientProp = $innerRef->getProperty('client');
                    $clientProp->setAccessible(true);
                    $inner = $clientProp->getValue($inner);
                }

                $optionsRef = new \ReflectionObject($inner);
                $optionsProp = $optionsRef->getProperty('defaultOptions');
                $optionsProp->setAccessible(true);
                $options = $optionsProp->getValue($inner);

                $this->assertContains('Authorization: Bearer token123', $options['headers']);
                break;
            }
        }

        // Verify caching and logging decorators are applied
        $provider = $securityProviders = $this->getInspectorProviders($facade->securityInspector)[0];
        $ref = new \ReflectionObject($provider);
        $clientProp = $ref->getProperty('client');
        $clientProp->setAccessible(true);
        $apiClient = $clientProp->getValue($provider);
        $httpRef = new \ReflectionObject($apiClient);
        $httpProp = $httpRef->getProperty('httpClient');
        $httpProp->setAccessible(true);
        $httpClient = $httpProp->getValue($apiClient);

        $this->assertInstanceOf(\PackApi\Http\Middleware\LoggingMiddleware::class, $httpClient);
        $innerRef = new \ReflectionObject($httpClient);
        $innerClientProp = $innerRef->getProperty('client');
        $innerClientProp->setAccessible(true);
        $innerClient = $innerClientProp->getValue($httpClient);
        $this->assertInstanceOf(\Symfony\Component\HttpClient\CachingHttpClient::class, $innerClient);
    }
}
