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

require_once __DIR__.'/../vendor/autoload.php';

use PackApi\Bridge\GitHub\GitHubProviderFactory;
use PackApi\Bridge\JsDelivr\JsDelivrProviderFactory;
use PackApi\Bridge\OSV\OSVProviderFactory;
use PackApi\Bridge\Packagist\PackagistProviderFactory;
use PackApi\Config\Configuration;
use PackApi\Http\HttpClientFactory;
use PackApi\Inspector\ActivityInspector;
use PackApi\Inspector\ContentInspector;
use PackApi\Inspector\DownloadStatsInspector;
use PackApi\Inspector\MetadataInspector;
use PackApi\Inspector\QualityInspector;
use PackApi\Inspector\SecurityInspector;
use PackApi\Package\ComposerPackage;

echo "PackApi - All Analysis Example\n";
echo "==============================\n\n";

// Setup
$httpFactory = new HttpClientFactory();
$httpClient = $httpFactory->createClient();
$config = new Configuration();

// Create provider factories
$packagistFactory = new PackagistProviderFactory($httpFactory);
$githubToken = getenv('GITHUB_TOKEN');
$githubFactory = new GitHubProviderFactory($httpFactory, $githubToken ?: null);
$jsdelivrFactory = new JsDelivrProviderFactory($httpFactory);
$osvFactory = new OSVProviderFactory($httpFactory);

// Create inspectors
$metadataInspector = new MetadataInspector([
    $packagistFactory->createMetadataProvider(),
    $githubFactory->createMetadataProvider(),
]);
$activityInspector = new ActivityInspector([
    $githubFactory->createActivityProvider(),
]);
$downloadStatsInspector = new DownloadStatsInspector([
    $packagistFactory->createStatsProvider(),
    $jsdelivrFactory->createStatsProvider(),
]);
$contentInspector = new ContentInspector([
    $githubFactory->createContentProvider(),
]);
$qualityInspector = new QualityInspector(
    $contentInspector,
    $metadataInspector
);
$securityInspector = new SecurityInspector([
    $osvFactory->createSecurityProvider(),
]);

// Analyze symfony/ux-live-component package
$package = new ComposerPackage('symfony/ux-live-component');

echo "Analyzing package: {$package->getName()}\n";
echo "Package identifier: {$package->getIdentifier()}\n\n";

// Metadata Analysis
echo "--- Metadata Analysis ---\n";
try {
    $metadata = $metadataInspector->getMetadata($package);
    if (null === $metadata) {
        echo "❌ No metadata found.\n";
    } else {
        echo "✅ Metadata found!\n";
        echo "  Name: {$metadata->name}\n";
        echo '  Description: '.($metadata->description ?? 'N/A')."\n";
        echo '  License: '.($metadata->license ?? 'N/A')."\n";
        echo '  Repository: '.($metadata->repository ?? 'N/A')."\n";
        // Set repository URL on package for later use
        if (!empty($metadata->repository)) {
            $package->setRepositoryUrl($metadata->repository);
        }
    }
} catch (Exception $e) {
    echo "❌ Error during metadata analysis: {$e->getMessage()}\n";
}
echo "\n";

// Activity Analysis
echo "--- Activity Analysis ---\n";
try {
    $activity = $activityInspector->getActivitySummary($package);
    if (null === $activity) {
        echo "❌ No activity summary found.\n";
    } else {
        echo "✅ Activity summary found!\n";
        echo '  Last Commit: '.($activity->lastCommit ? $activity->lastCommit->format('Y-m-d H:i:s') : 'N/A')."\n";
        echo '  Open Issues: '.($activity->openIssues ?? 'N/A')."\n";
        echo '  Open Pull Requests: '.($activity->openPullRequests ?? 'N/A')."\n";
    }
} catch (Exception $e) {
    echo "❌ Error during activity analysis: {$e->getMessage()}\n";
}
echo "\n";

// Download Stats Analysis
echo "--- Download Stats Analysis ---\n";
try {
    $downloadStats = $downloadStatsInspector->getStats($package);
    if (null === $downloadStats) {
        echo "❌ No download stats found.\n";
    } else {
        echo "✅ Download stats found!\n";
        foreach ($downloadStats->getPeriods() as $periodName => $period) {
            echo '  '.ucfirst($periodName).' Downloads: '.$period->getCount().' ('.$period->getStart()->format('Y-m-d').' to '.$period->getEnd()->format('Y-m-d').")\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error during download stats analysis: {$e->getMessage()}\n";
}
echo "\n";

// Content Analysis
echo "--- Content Analysis ---\n";
try {
    $contentOverview = $contentInspector->getContentOverview($package);
    if (null === $contentOverview) {
        echo "❌ No content overview found.\n";
    } else {
        echo "✅ Content overview found!\n";
        echo '  Total Files: '.($contentOverview->totalFiles ?? 'N/A')."\n";
        echo '  Total Lines: '.($contentOverview->totalLines ?? 'N/A')."\n";
        echo '  Languages: '.implode(', ', array_keys($contentOverview->languages ?? []))."\n";
    }
} catch (Exception $e) {
    echo "❌ Error during content analysis: {$e->getMessage()}\n";
}
echo "\n";

// Quality Analysis
echo "--- Quality Analysis ---\n";
try {
    $qualityScore = $qualityInspector->getQualityScore($package);
    if (null === $qualityScore) {
        echo "❌ No quality score found.\n";
    } else {
        echo "✅ Quality score found!\n";
        echo '  Score: '.($qualityScore->score ?? 'N/A')."\n";
        echo '  Popularity: '.($qualityScore->popularity ?? 'N/A')."\n";
        echo '  Maintenance: '.($qualityScore->maintenance ?? 'N/A')."\n";
    }
} catch (Exception $e) {
    echo "❌ Error during quality analysis: {$e->getMessage()}\n";
}
echo "\n";

// Security Analysis
echo "--- Security Analysis ---\n";
try {
    $securityAdvisories = $securityInspector->getSecurityAdvisories($package);
    if (empty($securityAdvisories)) {
        echo "✅ No security advisories found.\n";
    } else {
        echo "❌ Security advisories found!\n";
        foreach ($securityAdvisories as $advisory) {
            echo "  - ID: {$advisory->id}\n";
            echo "    Severity: {$advisory->severity}\n";
            echo "    Title: {$advisory->title}\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error during security analysis: {$e->getMessage()}\n";
}
echo "\n";

echo "✅ All analysis completed!\n";
