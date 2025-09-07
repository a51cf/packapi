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
use PackApi\Inspector\SecurityInspector;
use PackApi\Package\ComposerPackage;

echo "PackApi - Security Analysis Example\n";
echo "===================================\n\n";

// Setup
$httpFactory = new HttpClientFactory();
$config = new Configuration();

// Create provider factory
$packagistFactory = new PackagistProviderFactory($httpFactory, $config);

// Create security provider
$providers = [
    $packagistFactory->createSecurityProvider(),
];

// Create inspector
$inspector = new SecurityInspector($providers);

// Analyze symfony/ux-icons package
$package = new ComposerPackage('symfony/ux-twig-component');

echo "Analyzing security for: {$package->getName()}\n\n";

try {
    $advisories = $inspector->getSecurityAdvisories($package);

    if (null === $advisories || empty($advisories)) {
        echo "✅ No security advisories found for this package (good news!)\n";
    } else {
        echo "⚠️  Security advisories found!\n\n";

        foreach ($advisories as $advisory) {
            echo 'Advisory ID: '.($advisory->id ?? 'N/A')."\n";
            echo 'Severity: '.($advisory->severity ?? 'N/A')."\n";
            echo 'Summary: '.($advisory->summary ?? 'N/A')."\n";
            echo 'Published: '.($advisory->publishedAt ? $advisory->publishedAt->format('Y-m-d H:i:s') : 'N/A')."\n";
            echo 'Affected Versions: '.($advisory->affectedVersions ?? 'N/A')."\n";
            echo 'CVE: '.($advisory->cveId ?? 'N/A')."\n";
            echo "---\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    exit(1);
}

echo "\n✅ Security analysis completed successfully!\n";
