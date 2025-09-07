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
use PackApi\Inspector\ContentInspector;
use PackApi\Inspector\MetadataInspector;
use PackApi\Inspector\QualityInspector;
use PackApi\Package\ComposerPackage;

echo "PackApi - Quality Analysis Example\n";
echo "==================================\n\n";

// Setup
$httpFactory = new HttpClientFactory();

// Create provider factory
$packagistFactory = new PackagistProviderFactory($httpFactory);

// Create content and metadata inspectors
$contentProviders = [$packagistFactory->createContentProvider()];
$metadataProviders = [$packagistFactory->createMetadataProvider()];

$contentInspector = new ContentInspector($contentProviders);
$metadataInspector = new MetadataInspector($metadataProviders);

// Create quality inspector (without best practice provider for now)
$inspector = new QualityInspector($contentInspector, $metadataInspector);

// Analyze symfony/maker-bundle package
$package = new ComposerPackage('symfony/maker-bundle');

echo "Analyzing quality for: {$package->getName()}\n\n";

try {
    $quality = $inspector->getQualityScore($package);

    if (null === $quality) {
        echo "❌ No quality information found for this package\n";
        exit(1);
    }

    echo "✅ Quality analysis found!\n\n";
    echo 'Score: '.number_format($quality->score / 10, 1)."/10\n";
    if ($quality->grade) {
        echo 'Grade: '.$quality->grade."\n";
    }
    if ($quality->comment) {
        echo 'Comment: '.$quality->comment."\n";
    }
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    exit(1);
}

echo "\n✅ Quality analysis completed successfully!\n";
