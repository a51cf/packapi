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
use PackApi\Package\ComposerPackage;

echo "PackApi - Content Analysis Example\n";
echo "==================================\n\n";

// Setup
$httpFactory = new HttpClientFactory();

// Create provider factory
$packagistFactory = new PackagistProviderFactory($httpFactory);

// Create content provider
$providers = [
    $packagistFactory->createContentProvider(),
];

// Create inspector
$inspector = new ContentInspector($providers);

// Analyze symfony/maker-bundle package
$package = new ComposerPackage('symfony/maker-bundle');

echo "Analyzing content for: {$package->getName()}\n\n";

try {
    $content = $inspector->getContentOverview($package);

    if (null === $content) {
        echo "❌ No content information found for this package\n";
        exit(1);
    }

    echo "✅ Content analysis found!\n\n";

    echo 'File Count: '.number_format($content->fileCount)."\n";
    echo 'Total Size: '.number_format($content->totalSize)." bytes\n";
    echo 'Has README: '.($content->hasReadme() ? 'Yes' : 'No')."\n";
    echo 'Has License: '.($content->hasLicense() ? 'Yes' : 'No')."\n";
    echo 'Has Tests: '.($content->hasTests() ? 'Yes' : 'No')."\n";
    echo 'Has .gitignore: '.($content->hasGitignore() ? 'Yes' : 'No')."\n";
    echo 'Has .gitattributes: '.($content->hasGitattributes() ? 'Yes' : 'No')."\n";

    if (!empty($content->ignoredFiles)) {
        echo "\nIgnored Files:\n";
        foreach ($content->ignoredFiles as $file) {
            echo "  - $file\n";
        }
    }

    if (!empty($content->files)) {
        echo "\nSample Files (first 10):\n";
        $count = 0;
        foreach ($content->files as $file) {
            if ($count++ >= 10) {
                break;
            }
            echo "  - {$file->path} (".number_format($file->size)." bytes)\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    exit(1);
}

echo "\n✅ Content analysis completed successfully!\n";
