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

namespace PackApi\Bridge\JsDelivr;

use PackApi\Exception\NetworkException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class JsDelivrApiClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient, // This is now a scoped client
    ) {
    }

    /**
     * Fetch jsDelivr package metadata (e.g. for npm or composer).
     *
     * @param string $packageName e.g. "npm/lodash" or "gh/symfony/symfony"
     */
    public function fetchPackageMeta(string $packageName): ?array
    {
        try {
            // The base URI is already set by the scoped client (https://data.jsdelivr.com/)
            $response = $this->httpClient->request('GET', 'v1/package/'.$packageName);

            $statusCode = $response->getStatusCode();

            if (404 === $statusCode) {
                return null; // Package not found
            }

            if (200 !== $statusCode) {
                throw new NetworkException(sprintf('jsDelivr API returned status %d', $statusCode));
            }

            $content = $response->getContent(false);

            return $content ? json_decode($content, true, 512, JSON_THROW_ON_ERROR) : null;
        } catch (TransportExceptionInterface $e) {
            throw new NetworkException('Network error while calling jsDelivr API', 0, $e);
        }
    }

    /**
     * Fetch file list for a package version or root.
     *
     * @param string      $packageName e.g. "npm/lodash"
     * @param string|null $version     e.g. "4.17.21" or null for latest
     */
    public function fetchFileList(string $packageName, ?string $version = null): ?array
    {
        $ver = $version ? "@{$version}" : '';

        try {
            // The base URI is already set by the scoped client (https://data.jsdelivr.com/)
            $response = $this->httpClient->request('GET', 'v1/package/'.$packageName.$ver.'/flat');

            $statusCode = $response->getStatusCode();

            if (404 === $statusCode) {
                return null; // Package/version not found
            }

            if (200 !== $statusCode) {
                throw new NetworkException(sprintf('jsDelivr API returned status %d', $statusCode));
            }

            $content = $response->getContent(false);

            return $content ? json_decode($content, true, 512, JSON_THROW_ON_ERROR) : null;
        } catch (TransportExceptionInterface $e) {
            throw new NetworkException('Network error while calling jsDelivr API', 0, $e);
        }
    }
}
