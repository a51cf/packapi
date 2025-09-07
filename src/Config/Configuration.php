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

namespace PackApi\Config;

class Configuration
{
    public function __construct(
        public readonly array $github = [],
        public readonly array $packagist = [],
        public readonly array $npm = [],
        public readonly array $jsdelivr = [],
        public readonly array $cache = ['ttl' => 3600, 'type' => 'memory'],
        public readonly array $rateLimit = ['maxAttempts' => 60, 'period' => 3600],
        public readonly array $logger = ['file' => 'php://stdout'],
    ) {
    }
}
