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

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Factory for creating HTTP clients with standard configuration.
 */
interface HttpClientFactoryInterface
{
    /**
     * Create a Symfony HTTP client.
     *
     * Supported options:
     *   - "enable_quic" => bool  Enables HTTP/3 (QUIC) when true.
     *
     * @param array<string, mixed> $options
     */
    public function createClient(array $options = []): HttpClientInterface;
}
