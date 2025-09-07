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
use PackApi\Inspector\MetadataInspector;
use PackApi\Package\ComposerPackage;

echo "PackApi - Metadata Analysis Example\n";
echo "===================================\n\n";

// Setup
$httpFactory = new HttpClientFactory();

// Create provider factory
$packagistFactory = new PackagistProviderFactory($httpFactory);

// Create metadata providers
$providers = [
    $packagistFactory->createMetadataProvider(),
];

// Create inspector
$inspector = new MetadataInspector($providers);

// Analyze symfony/maker-bundle package
$package = new ComposerPackage('symfony/maker-bundle');

echo "Analyzing package: {$package->getName()}\n";
echo "Package identifier: {$package->getIdentifier()}\n\n";

try {
    $metadata = $inspector->getMetadata($package);

    if (null === $metadata) {
        echo "❌ No metadata found for this package\n";
        exit(1);
    }

    echo "✅ Metadata found!\n\n";
    echo "Name: {$metadata->name}\n";
    echo 'Description: '.($metadata->description ?? 'N/A')."\n";
    echo 'License: '.($metadata->license ?? 'N/A')."\n";
    echo 'Repository: '.($metadata->repository ?? 'N/A')."\n";
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    exit(1);
}

echo "\n✅ Metadata analysis completed successfully!\n";
