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

use Symfony\Component\Yaml\Yaml;

class ConfigLoader
{
    public function load(?string $configFile = null): Configuration
    {
        $config = [];

        if ($configFile && file_exists($configFile)) {
            $config = Yaml::parseFile($configFile);
        }

        // Merge with default configuration
        // get_object_vars is used instead of get_class_vars because the
        // readonly properties are initialized via the constructor.
        $defaultConfig = get_object_vars(new Configuration());

        $mergedConfig = array_replace_recursive($defaultConfig, $config);

        return new Configuration(
            github: $mergedConfig['github'] ?? [],
            packagist: $mergedConfig['packagist'] ?? [],
            npm: $mergedConfig['npm'] ?? [],
            jsdelivr: $mergedConfig['jsdelivr'] ?? [],
            cache: $mergedConfig['cache'] ?? [],
            rateLimit: $mergedConfig['rateLimit'] ?? [],
            logger: $mergedConfig['logger'] ?? []
        );
    }
}
