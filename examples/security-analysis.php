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
use PackApi\Inspector\SecurityInspector;
use PackApi\Package\ComposerPackage;

echo "PackApi - Security Analysis Example\n";
echo "===================================\n\n";

// Setup
$httpFactory = new HttpClientFactory();

// Create provider factory
$packagistFactory = new PackagistProviderFactory($httpFactory);

// Create security provider
$providers = [
    $packagistFactory->createSecurityProvider(),
];

// Create inspector
$inspector = new SecurityInspector($providers);

// Analyze symfony/maker-bundle package
$package = new ComposerPackage('symfony/ux-twig-component');

echo "Analyzing security for: {$package->getName()}\n\n";

try {
    $advisories = $inspector->getSecurityAdvisories($package);

    if (empty($advisories)) {
        echo "✅ No security advisories found for this package (good news!)\n";
    } else {
        echo "⚠️  Security advisories found!\n\n";

        foreach ($advisories as $advisory) {
            echo 'Advisory ID: '.$advisory->id."\n";
            echo 'Severity: '.$advisory->severity."\n";
            echo 'Title: '.$advisory->title."\n";
            echo 'Link: '.$advisory->link."\n";
            echo "---\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    exit(1);
}

echo "\n✅ Security analysis completed successfully!\n";
