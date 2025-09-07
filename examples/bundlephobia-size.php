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

use PackApi\Bridge\BundlePhobia\BundlePhobiaProviderFactory;
use PackApi\Http\HttpClientFactory;
use PackApi\Package\ComposerPackage;
use PackApi\Package\NpmPackage;

// Create HTTP client and factory
$httpFactory = new HttpClientFactory();

// Create BundlePhobia factory
$bundlePhobiaFactory = new BundlePhobiaProviderFactory($httpFactory);
$sizeProvider = $bundlePhobiaFactory->createBundleSizeProvider();

// Test with a popular NPM package
echo "=== BundlePhobia Analysis for NPM Package 'react' ===\n\n";
$npmPackage = new NpmPackage('react');

if ($sizeProvider->supports($npmPackage)) {
    $bundleSize = $sizeProvider->getBundleSize($npmPackage);

    if (null !== $bundleSize) {
        echo sprintf(
            "Package: %s@%s\n",
            $bundleSize->getName(),
            $bundleSize->getVersion()
        );

        if ($bundleSize->getDescription()) {
            echo sprintf("Description: %s\n", $bundleSize->getDescription());
        }

        echo sprintf(
            "Bundle Size: %s (%s gzipped)\n",
            $bundleSize->getFormattedSize(),
            $bundleSize->getFormattedGzipSize()
        );

        echo sprintf("Dependencies: %d\n", $bundleSize->getDependencyCount());

        if ($bundleSize->getDependencySize() > 0) {
            echo sprintf(
                "Dependency Size: %s\n",
                $bundleSize->getFormattedDependencySize()
            );
        }

        echo sprintf("Has JS Module: %s\n", $bundleSize->hasJSModule() ? 'Yes' : 'No');
        echo sprintf("Has Side Effects: %s\n", $bundleSize->hasSideEffects() ? 'Yes' : 'No');
        echo sprintf("Scoped Package: %s\n", $bundleSize->isScoped() ? 'Yes' : 'No');

        if ($bundleSize->getRepository()) {
            echo sprintf("Repository: %s\n", $bundleSize->getRepository());
        }
    } else {
        echo "No bundle size information found for react package.\n";
    }
} else {
    echo "Package type not supported by BundlePhobia provider.\n";
}

// Test with a specific version
echo "\n=== BundlePhobia Analysis for NPM Package 'lodash@4.17.21' ===\n\n";
$lodashPackage = new NpmPackage('lodash');

if ($sizeProvider->supports($lodashPackage)) {
    $bundleSize = $sizeProvider->getBundleSizeForVersion($lodashPackage, '4.17.21');

    if (null !== $bundleSize) {
        echo sprintf(
            "Package: %s@%s\n",
            $bundleSize->getName(),
            $bundleSize->getVersion()
        );

        echo sprintf(
            "Bundle Size: %s (%s gzipped)\n",
            $bundleSize->getFormattedSize(),
            $bundleSize->getFormattedGzipSize()
        );

        echo sprintf("Dependencies: %d\n", $bundleSize->getDependencyCount());
    } else {
        echo "No bundle size information found for lodash@4.17.21.\n";
    }
} else {
    echo "Package type not supported by BundlePhobia provider.\n";
}

// Test with Composer package (should not be supported)
echo "\n=== BundlePhobia Analysis for Composer Package 'symfony/maker-bundle' ===\n\n";
$composerPackage = new ComposerPackage('symfony/maker-bundle');

if ($sizeProvider->supports($composerPackage)) {
    echo "Composer package is supported (unexpected!).\n";
} else {
    echo "Composer packages are not supported by BundlePhobia (as expected).\n";
}
