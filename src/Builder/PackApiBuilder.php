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

namespace PackApi\Builder;

use PackApi\Bridge\BundlePhobia\BundlePhobiaProviderFactory;
use PackApi\Bridge\GitHub\GitHubProviderFactory;
use PackApi\Bridge\JsDelivr\JsDelivrProviderFactory;
use PackApi\Bridge\Npm\NpmProviderFactory;
use PackApi\Bridge\OSV\OSVProviderFactory;
use PackApi\Bridge\Packagist\PackagistProviderFactory;
use PackApi\Config\Configuration;
use PackApi\Http\HttpClientFactory;
use PackApi\Http\HttpClientFactoryInterface;
use PackApi\Inspector\ActivityInspector;
use PackApi\Inspector\ContentInspector;
use PackApi\Inspector\DownloadStatsInspector;
use PackApi\Inspector\MetadataInspector;
use PackApi\Inspector\PackageInspectorFacade;
use PackApi\Inspector\QualityInspector;
use PackApi\Inspector\SecurityInspector;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

/**
 * Fluent builder for creating a fully configured PackageInspectorFacade.
 */
final class PackApiBuilder
{
    private ?HttpClientFactoryInterface $httpClientFactory;
    private ?string $githubToken = null;
    private ?CacheItemPoolInterface $cachePool = null;
    private ?LoggerInterface $logger = null;

    public function __construct(
        Configuration $configuration = new Configuration(),
        ?HttpClientFactoryInterface $httpClientFactory = null,
    ) {
        $this->httpClientFactory = $httpClientFactory;
    }

    public function withGitHubToken(?string $token): self
    {
        $this->githubToken = $token;

        return $this;
    }

    public function useCache(CacheItemPoolInterface $cache): self
    {
        $this->cachePool = $cache;

        return $this;
    }

    public function withLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function build(): PackageInspectorFacade
    {
        $httpFactory = $this->httpClientFactory ?? new HttpClientFactory(
            logger: $this->logger,
            cachePool: $this->cachePool,
        );

        $packagistFactory = new PackagistProviderFactory($httpFactory);
        $githubFactory = new GitHubProviderFactory($httpFactory, $this->githubToken);
        $jsDelivrFactory = new JsDelivrProviderFactory($httpFactory);
        $npmFactory = new NpmProviderFactory($httpFactory);
        $osvFactory = new OSVProviderFactory($httpFactory);
        $bundlephobiaFactory = new BundlePhobiaProviderFactory($httpFactory);

        $metadataInspector = new MetadataInspector([
            $packagistFactory->createMetadataProvider(),
            $npmFactory->createMetadataProvider(),
            $githubFactory->createMetadataProvider(),
            $jsDelivrFactory->createMetadataProvider(),
        ]);

        $downloadStatsInspector = new DownloadStatsInspector([
            $packagistFactory->createStatsProvider(),
            $npmFactory->createDownloadStatsProvider(),
            $jsDelivrFactory->createStatsProvider(),
            $githubFactory->createStatisticProvider(),
        ]);

        $contentInspector = new ContentInspector([
            $packagistFactory->createContentProvider(),
            $npmFactory->createContentProvider(),
            $jsDelivrFactory->createContentProvider(),
            $githubFactory->createContentProvider(),
        ]);

        $activityInspector = new ActivityInspector([
            $githubFactory->createActivityProvider(),
            $packagistFactory->createActivityProvider(),
        ]);

        $securityInspector = new SecurityInspector([
            $osvFactory->createSecurityProvider(),
            $githubFactory->createSecurityProvider(),
            $packagistFactory->createSecurityProvider(),
        ]);

        $qualityInspector = new QualityInspector($contentInspector, $metadataInspector);

        return new PackageInspectorFacade(
            $metadataInspector,
            $downloadStatsInspector,
            $contentInspector,
            $activityInspector,
            $securityInspector,
            $qualityInspector,
        );
    }
}
