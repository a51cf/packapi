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

namespace PackApi\Bridge\OSV;

use PackApi\Model\SecurityAdvisory;
use PackApi\Package\ComposerPackage;
use PackApi\Package\NpmPackage;
use PackApi\Package\Package;
use PackApi\Provider\SecurityProviderInterface;

final class OSVSecurityProvider implements SecurityProviderInterface
{
    public function __construct(private readonly OSVApiClient $client)
    {
    }

    public function supports(Package $package): bool
    {
        return $package instanceof ComposerPackage || $package instanceof NpmPackage;
    }

    /**
     * @return SecurityAdvisory[]
     */
    public function getSecurityAdvisories(Package $package): array
    {
        $ecosystem = $this->getEcosystemForPackage($package);
        if (null === $ecosystem) {
            return [];
        }

        $packageName = $this->getPackageNameForOSV($package);

        try {
            $data = $this->client->queryVulnerabilities($ecosystem, $packageName);

            if (null === $data || !isset($data['vulns']) || empty($data['vulns'])) {
                return [];
            }

            $advisories = [];
            foreach ($data['vulns'] as $vulnerability) {
                $advisory = $this->createSecurityAdvisoryFromOSVData($vulnerability);
                if (null !== $advisory) {
                    $advisories[] = $advisory;
                }
            }

            return $advisories;
        } catch (\Exception $e) {
            // Log error but don't throw - security checks should be non-blocking
            return [];
        }
    }

    /**
     * Get advisories for a specific version of a package.
     *
     * @return SecurityAdvisory[]|null
     */
    public function getSecurityAdvisoriesForVersion(Package $package, string $version): ?array
    {
        $ecosystem = $this->getEcosystemForPackage($package);
        if (null === $ecosystem) {
            return null;
        }

        $packageName = $this->getPackageNameForOSV($package);

        try {
            $data = $this->client->queryVulnerabilities($ecosystem, $packageName, $version);

            if (null === $data || !isset($data['vulns']) || empty($data['vulns'])) {
                return [];
            }

            $advisories = [];
            foreach ($data['vulns'] as $vulnerability) {
                $advisory = $this->createSecurityAdvisoryFromOSVData($vulnerability);
                if (null !== $advisory) {
                    $advisories[] = $advisory;
                }
            }

            return $advisories;
        } catch (\Exception $e) {
            // Log error but don't throw - security checks should be non-blocking
            return null;
        }
    }

    private function getEcosystemForPackage(Package $package): ?string
    {
        return match (true) {
            $package instanceof ComposerPackage => 'Packagist',
            $package instanceof NpmPackage => 'npm',
            default => null,
        };
    }

    private function getPackageNameForOSV(Package $package): string
    {
        // For most packages, the identifier is the correct name for OSV
        return $package->getIdentifier();
    }

    private function createSecurityAdvisoryFromOSVData(array $vulnerability): ?SecurityAdvisory
    {
        // OSV vulnerability structure
        if (!isset($vulnerability['id'])) {
            return null;
        }

        $id = $vulnerability['id'];
        $title = $vulnerability['summary'] ?? $vulnerability['details'] ?? 'Security vulnerability';

        // Extract severity from database_specific or severity field
        $severity = $this->extractSeverity($vulnerability);

        // Generate link to OSV vulnerability page
        $link = 'https://osv.dev/vulnerability/'.urlencode($id);

        return new SecurityAdvisory(
            id: $id,
            title: $title,
            severity: $severity,
            link: $link
        );
    }

    private function extractSeverity(array $vulnerability): string
    {
        // Try to extract severity from various possible locations

        // Check for CVSS severity
        if (isset($vulnerability['severity'])) {
            foreach ($vulnerability['severity'] as $severityEntry) {
                if (isset($severityEntry['score'])) {
                    $score = (float) $severityEntry['score'];

                    return $this->cvssScoreToSeverity($score);
                }
            }
        }

        // Check database_specific for severity information
        if (isset($vulnerability['database_specific']['severity'])) {
            return strtoupper($vulnerability['database_specific']['severity']);
        }

        // Check for GitHub Security Advisory severity
        if (isset($vulnerability['database_specific']['github_reviewed'])
            && isset($vulnerability['database_specific']['severity'])) {
            return strtoupper($vulnerability['database_specific']['severity']);
        }

        // Default to MEDIUM if no severity information is available
        return 'MEDIUM';
    }

    private function cvssScoreToSeverity(float $score): string
    {
        return match (true) {
            $score >= 9.0 => 'CRITICAL',
            $score >= 7.0 => 'HIGH',
            $score >= 4.0 => 'MEDIUM',
            default => 'LOW',
        };
    }

    /**
     * Check if a specific vulnerability ID affects the package.
     */
    public function isVulnerabilityRelevant(Package $package, string $vulnId): bool
    {
        try {
            $vulnerability = $this->client->getVulnerabilityById($vulnId);

            if (null === $vulnerability || !isset($vulnerability['affected'])) {
                return false;
            }

            $ecosystem = $this->getEcosystemForPackage($package);
            $packageName = $this->getPackageNameForOSV($package);

            foreach ($vulnerability['affected'] as $affected) {
                if (isset($affected['package'])) {
                    $affectedPackage = $affected['package'];
                    if ($affectedPackage['ecosystem'] === $ecosystem
                        && $affectedPackage['name'] === $packageName) {
                        return true;
                    }
                }
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get detailed vulnerability information by ID.
     */
    public function getVulnerabilityDetails(string $vulnId): ?array
    {
        try {
            return $this->client->getVulnerabilityById($vulnId);
        } catch (\Exception $e) {
            return null;
        }
    }
}
