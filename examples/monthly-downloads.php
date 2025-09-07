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

use PackApi\Bridge\Packagist\PackagistApiClient;
use PackApi\Http\HttpClientFactory;
use PackApi\Package\ComposerPackage;

echo "PackApi - Monthly Downloads Example\n";
echo "===================================\n\n";

// Get package name from CLI argument or use default
$packageName = $_SERVER['argv'][1] ?? 'symfony/ux-live-component';

// Setup
$httpFactory = new HttpClientFactory();
$httpClient = $httpFactory->createClient();

// Create Packagist API client
$packagistApiClient = new PackagistApiClient(
    $httpClient->withOptions([
        'base_uri' => 'https://packagist.org/',
        'headers' => [
            'User-Agent' => 'PackApi/1.0',
        ],
        'timeout' => 30,
    ])
);

// Analyze package
$package = new ComposerPackage($packageName);

echo "Analyzing package: {$package->getName()}\n";
echo "Package identifier: {$package->getIdentifier()}\n\n";

try {
    $from = new DateTimeImmutable('2021-06-18'); // Start date as per your example
    $historicalData = $packagistApiClient->fetchHistoricalMonthlyDownloads(
        $package->getIdentifier(),
        $from
    );

    if (isset($historicalData['labels']) && isset($historicalData['values']['2'])) {
        echo "✅ Historical monthly download stats found!\n\n";

        $labels = $historicalData['labels'];
        $values = $historicalData['values']['2']; // Assuming '2' is the correct key for the values

        // Get the last 12 months of data
        $last12Labels = array_slice($labels, -12);
        $last12Values = array_slice($values, -12);

        $formattedHeaders = [];
        foreach ($last12Labels as $label) {
            $date = new DateTimeImmutable($label.'-01'); // Add day to parse correctly
            $formattedHeaders[] = $date->format('Y-m');
        }

        // Calculate max width for each column
        $columnWidths = [];
        foreach ($formattedHeaders as $index => $header) {
            $columnWidths[$index] = max(strlen($header), strlen((string) $last12Values[$index]));
        }

        // Prepare markdown table headers with padding
        $paddedHeaders = [];
        foreach ($formattedHeaders as $index => $header) {
            $paddedHeaders[] = str_pad($header, $columnWidths[$index], ' ', STR_PAD_RIGHT);
        }
        echo '| '.implode(' | ', $paddedHeaders)." |\n";

        // Prepare markdown table rows with right alignment
        $paddedValues = [];
        foreach ($last12Values as $index => $value) {
            $paddedValues[] = str_pad((string) $value, $columnWidths[$index], ' ', STR_PAD_LEFT);
        }
        echo '| '.implode(' | ', $paddedValues)." |\n";
    } else {
        echo "❌ No historical monthly download stats found for this package or period.\n";
    }
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    exit(1);
}

echo "\n✅ Monthly download analysis completed successfully!\n";
