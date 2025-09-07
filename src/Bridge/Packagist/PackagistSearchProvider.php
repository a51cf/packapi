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

use PackApi\Provider\PackageSearchInterface;

final class PackagistSearchProvider implements PackageSearchInterface
{
    public function __construct(private readonly PackagistApiClient $client)
    {
    }

    public function search(string $query, int $limit = 20): array
    {
        $data = $this->client->searchPackages($query, $limit);

        $results = [];
        foreach ($data['results'] ?? [] as $result) {
            $results[] = [
                'identifier' => $result['name'],
                'name' => $result['name'],
                'description' => $result['description'] ?? null,
                'repository' => $result['repository'] ?? null,
            ];
        }

        return $results;
    }

    public function searchByKeyword(string $keyword): array
    {
        return $this->search($keyword);
    }

    public function getPopular(int $limit = 50): array
    {
        // Packagist API does not have a direct 'popular' endpoint.
        // We can simulate by fetching packages with high downloads.
        // This would require more complex logic, for now, return empty.
        return [];
    }
}
