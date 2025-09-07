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

namespace PackApi\Security;

interface SecureFileHandlerInterface
{
    public function downloadSafely(string $url, int $maxSize = 104857600): string;

    /**
     * @return string[] Relative paths of extracted files
     */
    public function extractSafely(string $archivePath, string $destination): array;

    public function validatePath(string $path): bool;
}
