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
use PackApi\Inspector\DownloadStatsInspector;
use PackApi\Package\ComposerPackage;

echo "PackApi - Download Statistics Analysis Example\n";
echo "=============================================\n\n";

// Setup
$httpFactory = new HttpClientFactory();

// Create provider factory
$packagistFactory = new PackagistProviderFactory($httpFactory);

// Create download stats provider
$providers = [
    $packagistFactory->createStatsProvider(),
];

// Create inspector
$inspector = new DownloadStatsInspector($providers);

// Analyze symfony/maker-bundle package
$package = new ComposerPackage('symfony/maker-bundle');

echo "Analyzing download statistics for: {$package->getName()}\n\n";

try {
    // Get default stats (usually last month)
    $stats = $inspector->getStats($package);

    if (null === $stats) {
        echo "❌ No download statistics found for this package\n";
        exit(1);
    }

    echo "✅ Download statistics found!\n\n";

    // Display available periods
    foreach ($stats->getPeriods() as $key => $period) {
        echo ucfirst($key).' Downloads: '.number_format($period->count)."\n";
        echo '  Period: '.$period->start->format('Y-m-d').' to '.$period->end->format('Y-m-d')."\n\n";
    }
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    exit(1);
}

echo "\n✅ Download statistics analysis completed successfully!\n";
