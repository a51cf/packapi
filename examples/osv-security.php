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

use PackApi\Bridge\OSV\OSVProviderFactory;
use PackApi\Http\HttpClientFactory;
use PackApi\Package\ComposerPackage;
use PackApi\Package\NpmPackage;

// Create HTTP client and factory
$httpFactory = new HttpClientFactory();

// Create OSV factory
$osvFactory = new OSVProviderFactory($httpFactory);
$securityProvider = $osvFactory->createSecurityProvider();

// Test with a NPM package known to have vulnerabilities
echo "=== OSV Security Analysis for NPM Package 'lodash' ===\n\n";
$npmPackage = new NpmPackage('lodash');

if ($securityProvider->supports($npmPackage)) {
    $advisories = $securityProvider->getSecurityAdvisories($npmPackage);

    if (!empty($advisories)) {
        echo 'Found '.count($advisories)." security advisories:\n\n";

        foreach ($advisories as $advisory) {
            echo sprintf(
                "- %s [%s] %s\n  Link: %s\n\n",
                $advisory->getId(),
                $advisory->getSeverity(),
                $advisory->getTitle(),
                $advisory->getLink()
            );
        }
    } else {
        echo "No vulnerabilities found for lodash package.\n";
    }
} else {
    echo "Package type not supported by OSV provider.\n";
}

// Test with a Composer package
echo "\n=== OSV Security Analysis for Composer Package 'symfony/maker-bundle' ===\n\n";
$composerPackage = new ComposerPackage('symfony/maker-bundle');

if ($securityProvider->supports($composerPackage)) {
    $advisories = $securityProvider->getSecurityAdvisories($composerPackage);

    if (!empty($advisories)) {
        echo 'Found '.count($advisories)." security advisories:\n\n";

        foreach ($advisories as $advisory) {
            echo sprintf(
                "- %s [%s] %s\n  Link: %s\n\n",
                $advisory->getId(),
                $advisory->getSeverity(),
                $advisory->getTitle(),
                $advisory->getLink()
            );
        }
    } else {
        echo "No vulnerabilities found for symfony/maker-bundle package.\n";
    }
} else {
    echo "Package type not supported by OSV provider.\n";
}
