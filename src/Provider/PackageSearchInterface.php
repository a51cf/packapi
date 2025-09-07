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

namespace PackApi\Provider;

interface PackageSearchInterface
{
    /**
     * Searches for packages based on a query string.
     *
     * @return array<int, array{identifier: string, name: string, description?: string, repository?: string}>
     */
    public function search(string $query, int $limit = 20): array;

    /**
     * Searches for packages based on a keyword.
     *
     * @return array<int, array{identifier: string, name: string, description?: string, repository?: string}>
     */
    public function searchByKeyword(string $keyword): array;

    /**
     * Returns a list of popular packages.
     *
     * @return array<int, array{identifier: string, name: string, description?: string, repository?: string}>
     */
    public function getPopular(int $limit = 50): array;
}
