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

namespace PackApi\Bridge\Packagist;

use PackApi\Exception\ValidationException;
use PackApi\Model\ContentOverview;
use PackApi\Model\File;
use PackApi\Package\Package;
use PackApi\Provider\ContentProviderInterface;
use PackApi\Security\SecureFileHandler;

final class PackagistContentProvider implements ContentProviderInterface
{
    public function __construct(
        private readonly PackagistApiClient $client,
        private readonly SecureFileHandler $fileHandler,
    ) {
    }

    public function supports(Package $package): bool
    {
        // Only support Composer packages for now
        return $package->getIdentifier() && str_contains($package->getIdentifier(), '/');
    }

    public function getContentOverview(Package $package): ?ContentOverview
    {
        $data = $this->client->fetchPackage($package->getIdentifier());
        if (empty($data['package']['versions'])) {
            return null;
        }
        // Find the latest stable version
        $versions = $data['package']['versions'];
        uksort($versions, 'version_compare');
        $latest = end($versions);
        if (empty($latest['dist']['url'])) {
            return null;
        }
        try {
            $distUrl = $latest['dist']['url'];

            // Securely download the tarball
            $tarPath = $this->fileHandler->downloadSafely($distUrl);

            // Create secure extraction directory
            $tmp = sys_get_temp_dir().'/packapi_'.uniqid();
            if (!mkdir($tmp, 0755, true)) {
                throw new ValidationException('Cannot create extraction directory');
            }

            // Securely extract the archive
            $extractedFiles = $this->fileHandler->extractSafely($tarPath, $tmp);

            // Scan files safely
            $files = [];
            $rii = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($tmp, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($rii as $file) {
                if ($file->isFile()) {
                    $relPath = ltrim(str_replace($tmp, '', $file->getPathname()), '/\\');

                    // Validate file path for security
                    if (!$this->fileHandler->validatePath($relPath)) {
                        continue;
                    }

                    $files[] = new File(
                        $relPath,
                        $file->getSize(),
                        false,
                        \DateTimeImmutable::createFromFormat('U', (string) $file->getMTime())
                    );
                }
            }
        } catch (ValidationException $e) {
            return null; // Return null for validation errors instead of throwing
        } finally {
            // Ensure cleanup happens even if extraction fails
            if (isset($tarPath) && file_exists($tarPath)) {
                unlink($tarPath);
            }
            if (isset($tmp) && is_dir($tmp)) {
                $this->cleanupDirectory($tmp);
            }
        }
        $fileCount = count($files);
        $totalSize = array_sum(array_map(fn ($f) => $f->getSize(), $files));
        $hasReadme = (bool) array_filter($files, fn ($f) => false !== stripos($f->getPath(), 'readme'));
        $hasLicense = (bool) array_filter($files, fn ($f) => false !== stripos($f->getPath(), 'license'));
        $hasTests = (bool) array_filter($files, fn ($f) => false !== stripos($f->getPath(), 'test'));
        $hasGitattributes = (bool) array_filter($files, fn ($f) => '.gitattributes' === $f->getPath());
        $hasGitignore = (bool) array_filter($files, fn ($f) => '.gitignore' === $f->getPath());
        $ignoredFiles = array_values(array_filter(
            array_map(fn ($f) => $f->getPath(), $files),
            fn ($name) => preg_match('/(example|demo|sample|docs|test)/i', $name)
        ));

        return new ContentOverview(
            $fileCount,
            $totalSize,
            $hasReadme,
            $hasLicense,
            $hasTests,
            $ignoredFiles,
            $files,
            $hasGitattributes,
            $hasGitignore
        );
    }

    private function cleanupDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileInfo) {
            if ($fileInfo->isDir()) {
                rmdir($fileInfo->getRealPath());
            } else {
                unlink($fileInfo->getRealPath());
            }
        }
        rmdir($path);
    }
}
