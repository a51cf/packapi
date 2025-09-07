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

namespace PackApi\Http;

use PackApi\Http\Middleware\LoggingMiddleware;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * HTTP client factory that creates a decorated client with caching and logging middleware.
 */
final class HttpClientFactory implements HttpClientFactoryInterface
{
    public function __construct(
        private readonly ?LoggerInterface $logger = null,
        private readonly ?CacheItemPoolInterface $cachePool = null,
    ) {
    }

    public function createClient(array $options = []): HttpClientInterface
    {
        $httpOptions = [];

        if (!empty($options['enable_quic'])) {
            $httpOptions['http_version'] = '3';
            // Remove the custom option before passing to HttpClient
            unset($options['enable_quic']);
        }

        // Merge any additional options (after removing custom ones)
        $httpOptions = array_merge($httpOptions, $options);

        $client = HttpClient::create($httpOptions);

        // Decorate with caching if available
        if ($this->cachePool && class_exists(\Symfony\Component\HttpClient\CachingHttpClient::class)) {
            $client = new \Symfony\Component\HttpClient\CachingHttpClient($client, $this->cachePool);
        }

        // Decorate with logging last to see the final request
        if ($this->logger) {
            $client = new LoggingMiddleware($client, $this->logger);
        }

        return $client;
    }
}
