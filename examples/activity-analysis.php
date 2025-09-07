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

use PackApi\Bridge\Packagist\PackagistProviderFactory;
use PackApi\Config\Configuration;
use PackApi\Http\HttpClientFactory;
use PackApi\Inspector\ActivityInspector;
use PackApi\Package\ComposerPackage;

echo "PackApi - Activity Analysis Example\n";
echo "===================================\n\n";

// Setup
$httpFactory = new HttpClientFactory();
$config = new Configuration();

// Create provider factory
$packagistFactory = new PackagistProviderFactory($httpFactory, $config);

// Create activity provider
$providers = [
    $packagistFactory->createActivityProvider(),
];

// Create inspector
$inspector = new ActivityInspector($providers);

// Analyze symfony/ux-icons package
$package = new ComposerPackage('symfony/ux-icons');

echo "Analyzing activity for: {$package->getName()}\n\n";

try {
    $activity = $inspector->getActivitySummary($package);

    if (null === $activity) {
        echo "❌ No activity information found for this package\n";
        exit(1);
    }

    echo "✅ Activity analysis found!\n\n";

    if (isset($activity->lastCommitDate)) {
        echo 'Last Commit: '.$activity->lastCommitDate->format('Y-m-d H:i:s')."\n";
    }

    if (isset($activity->totalCommits)) {
        echo 'Total Commits: '.number_format($activity->totalCommits)."\n";
    }

    if (isset($activity->activeContributors)) {
        echo 'Active Contributors: '.number_format($activity->activeContributors)."\n";
    }

    if (isset($activity->averageTimeToClose)) {
        echo 'Average Time to Close Issues: '.number_format($activity->averageTimeToClose)." days\n";
    }

    if (isset($activity->commitFrequency)) {
        echo 'Commit Frequency: '.number_format($activity->commitFrequency, 2)." commits/day\n";
    }

    if (isset($activity->lastReleaseDate)) {
        echo 'Last Release: '.$activity->lastReleaseDate->format('Y-m-d H:i:s')."\n";
    }

    if (isset($activity->releaseFrequency)) {
        echo 'Release Frequency: '.number_format($activity->releaseFrequency, 2)." releases/month\n";
    }

    if (isset($activity->openIssues)) {
        echo 'Open Issues: '.number_format($activity->openIssues)."\n";
    }

    if (isset($activity->closedIssues)) {
        echo 'Closed Issues: '.number_format($activity->closedIssues)."\n";
    }
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    exit(1);
}

echo "\n✅ Activity analysis completed successfully!\n";
