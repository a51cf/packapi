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
use PackApi\Http\HttpClientFactory;
use PackApi\Inspector\ActivityInspector;
use PackApi\Package\ComposerPackage;

echo "PackApi - Activity Analysis Example\n";
echo "===================================\n\n";

// Setup
$httpFactory = new HttpClientFactory();

// Create provider factory
$packagistFactory = new PackagistProviderFactory($httpFactory);

// Create activity provider
$providers = [
    $packagistFactory->createActivityProvider(),
];

// Create inspector
$inspector = new ActivityInspector($providers);

// Analyze symfony/maker-bundle package
$package = new ComposerPackage('symfony/maker-bundle');

echo "Analyzing activity for: {$package->getName()}\n\n";

try {
    $activity = $inspector->getActivitySummary($package);

    if (null === $activity) {
        echo "❌ No activity information found for this package\n";
        exit(1);
    }

    echo "✅ Activity analysis found!\n\n";

    if ($activity->lastCommit) {
        echo 'Last Commit: '.$activity->lastCommit->format('Y-m-d H:i:s')."\n";
    }

    echo 'Contributors: '.number_format($activity->contributors)."\n";
    echo 'Open Issues: '.number_format($activity->openIssues)."\n";

    if ($activity->lastRelease) {
        echo 'Last Release: '.$activity->lastRelease."\n";
    }
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    exit(1);
}

echo "\n✅ Activity analysis completed successfully!\n";
