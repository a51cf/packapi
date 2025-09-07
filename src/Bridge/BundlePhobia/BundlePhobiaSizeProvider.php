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

namespace PackApi\Bridge\BundlePhobia;

use PackApi\Model\BundleSize;
use PackApi\Package\NpmPackage;
use PackApi\Package\Package;
use PackApi\Provider\BundleSizeProviderInterface;

final class BundlePhobiaSizeProvider implements BundleSizeProviderInterface
{
    public function __construct(private readonly BundlePhobiaApiClient $client)
    {
    }

    public function supports(Package $package): bool
    {
        // BundlePhobia only supports NPM packages
        return $package instanceof NpmPackage;
    }

    public function getBundleSize(Package $package): ?BundleSize
    {
        try {
            $data = $this->client->getBundleSize($package->getName());

            if (null === $data) {
                return null;
            }

            return $this->createBundleSizeFromData($data);
        } catch (\Exception $e) {
            // Log error but don't throw - bundle analysis should be non-blocking
            return null;
        }
    }

    public function getBundleSizeForVersion(Package $package, string $version): ?BundleSize
    {
        try {
            $data = $this->client->getBundleSize($package->getName(), $version);

            if (null === $data) {
                return null;
            }

            return $this->createBundleSizeFromData($data);
        } catch (\Exception $e) {
            // Log error but don't throw - bundle analysis should be non-blocking
            return null;
        }
    }

    /**
     * Get package history showing size evolution over versions.
     */
    public function getPackageHistory(Package $package): ?array
    {
        if (!$this->supports($package)) {
            return null;
        }

        try {
            return $this->client->getPackageHistory($package->getName());
        } catch (\Exception $e) {
            return null;
        }
    }

    private function createBundleSizeFromData(array $data): BundleSize
    {
        return new BundleSize(
            name: $data['name'] ?? 'unknown',
            version: $data['version'] ?? 'unknown',
            description: $data['description'] ?? null,
            size: (int) ($data['size'] ?? 0),
            gzip: (int) ($data['gzip'] ?? 0),
            dependencyCount: (int) ($data['dependencyCount'] ?? 0),
            dependencySize: (int) ($data['dependencySizes'] ?? 0),
            hasJSModule: (bool) ($data['hasJSModule'] ?? false),
            hasSideEffects: (bool) ($data['hasSideEffects'] ?? false),
            isScoped: (bool) ($data['scoped'] ?? false),
            repository: $data['repository']['url'] ?? null,
        );
    }
}
