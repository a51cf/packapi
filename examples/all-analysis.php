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
    // Try GitHub first for richer activity
    $githubFactory->createActivityProvider(),
    // Fallback to Packagist release activity if GitHub fails/unavailable
    $packagistFactory->createActivityProvider(),
]);
$downloadStatsInspector = new DownloadStatsInspector([
    $packagistFactory->createStatsProvider(),
    $jsdelivrFactory->createStatsProvider(),
]);
$contentInspector = new ContentInspector([
    // Try GitHub contents API first
    $githubFactory->createContentProvider(),
    // Fallback to Packagist archive analysis when GitHub is unavailable
    $packagistFactory->createContentProvider(),
]);
$qualityInspector = new QualityInspector(
    $contentInspector,
    $metadataInspector
);
$securityInspector = new SecurityInspector([
    $osvFactory->createSecurityProvider(),
]);

// Analyze symfony/ux-live-component package
$package = new ComposerPackage('symfony/maker-bundle');

echo "Analyzing package: {$package->getName()}\n";
echo "Package identifier: {$package->getIdentifier()}\n\n";

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

echo "--- Activity Analysis ---\n";
try {
    $activity = $activityInspector->getActivitySummary($package);
    if (null === $activity) {
        echo "❌ No activity summary found.\n";
    } else {
        echo "✅ Activity summary found!\n";
        echo '  Last Commit: '.($activity->lastCommit ? $activity->lastCommit->format('Y-m-d H:i:s') : 'N/A')."\n";
        echo '  Contributors: '.number_format($activity->contributors)."\n";
        echo '  Open Issues: '.number_format($activity->openIssues)."\n";
        echo '  Last Release: '.($activity->lastRelease ?? 'N/A')."\n";
    }
} catch (Exception $e) {
    echo "❌ Error during activity analysis: {$e->getMessage()}\n";
}
echo "\n";

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

echo "--- Content Analysis ---\n";
try {
    $contentOverview = $contentInspector->getContentOverview($package);
    if (null === $contentOverview) {
        echo "❌ No content overview found.\n";
    } else {
        echo "✅ Content overview found!\n";
        echo '  File Count: '.number_format($contentOverview->fileCount)."\n";
        echo '  Total Size: '.number_format($contentOverview->totalSize)." bytes\n";
        echo '  Has README: '.($contentOverview->hasReadme ? 'Yes' : 'No')."\n";
        echo '  Has License: '.($contentOverview->hasLicense ? 'Yes' : 'No')."\n";
    }
} catch (Exception $e) {
    echo "❌ Error during content analysis: {$e->getMessage()}\n";
}
echo "\n";

echo "--- Quality Analysis ---\n";
try {
    $qualityScore = $qualityInspector->getQualityScore($package);
    if (null === $qualityScore) {
        echo "❌ No quality score found.\n";
    } else {
        echo "✅ Quality score found!\n";
        echo '  Score: '.number_format($qualityScore->score / 10, 1)."/10\n";
        if ($qualityScore->grade) {
            echo '  Grade: '.$qualityScore->grade."\n";
        }
        if ($qualityScore->comment) {
            echo '  Comment: '.$qualityScore->comment."\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error during quality analysis: {$e->getMessage()}\n";
}
echo "\n";

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
