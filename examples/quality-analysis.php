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
use PackApi\Inspector\ContentInspector;
use PackApi\Inspector\MetadataInspector;
use PackApi\Inspector\QualityInspector;
use PackApi\Package\ComposerPackage;

echo "PackApi - Quality Analysis Example\n";
echo "==================================\n\n";

// Setup
$httpFactory = new HttpClientFactory();
$config = new Configuration();

// Create provider factory
$packagistFactory = new PackagistProviderFactory($httpFactory, $config);

// Create content and metadata inspectors
$contentProviders = [$packagistFactory->createContentProvider()];
$metadataProviders = [$packagistFactory->createMetadataProvider()];

$contentInspector = new ContentInspector($contentProviders);
$metadataInspector = new MetadataInspector($metadataProviders);

// Create quality inspector (without best practice provider for now)
$inspector = new QualityInspector($contentInspector, $metadataInspector);

// Analyze symfony/ux-icons package
$package = new ComposerPackage('symfony/ux-icons');

echo "Analyzing quality for: {$package->getName()}\n\n";

try {
    $quality = $inspector->getQualityScore($package);

    if (null === $quality) {
        echo "❌ No quality information found for this package\n";
        exit(1);
    }

    echo "✅ Quality analysis found!\n\n";

    echo 'Overall Score: '.number_format($quality->overallScore, 2)."/10\n";

    if (isset($quality->codeQuality)) {
        echo 'Code Quality: '.number_format($quality->codeQuality, 2)."/10\n";
    }

    if (isset($quality->documentation)) {
        echo 'Documentation: '.number_format($quality->documentation, 2)."/10\n";
    }

    if (isset($quality->testCoverage)) {
        echo 'Test Coverage: '.number_format($quality->testCoverage, 2)."/10\n";
    }

    if (isset($quality->maintainability)) {
        echo 'Maintainability: '.number_format($quality->maintainability, 2)."/10\n";
    }

    if (isset($quality->security)) {
        echo 'Security: '.number_format($quality->security, 2)."/10\n";
    }

    if (isset($quality->popularity)) {
        echo 'Popularity: '.number_format($quality->popularity, 2)."/10\n";
    }

    if (!empty($quality->recommendations)) {
        echo "\nRecommendations:\n";
        foreach ($quality->recommendations as $recommendation) {
            echo "  - $recommendation\n";
        }
    }

    if (!empty($quality->strengths)) {
        echo "\nStrengths:\n";
        foreach ($quality->strengths as $strength) {
            echo "  + $strength\n";
        }
    }

    if (!empty($quality->weaknesses)) {
        echo "\nWeaknesses:\n";
        foreach ($quality->weaknesses as $weakness) {
            echo "  - $weakness\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    exit(1);
}

echo "\n✅ Quality analysis completed successfully!\n";
