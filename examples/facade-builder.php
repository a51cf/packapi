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

require_once __DIR__.'/../vendor/autoload.php';

use PackApi\Builder\PackApiBuilder;
use PackApi\Package\ComposerPackage;

$facade = (new PackApiBuilder())
    ->withGitHubToken($_ENV['GITHUB_TOKEN'] ?? null)
    ->build();

$result = $facade->analyze(new ComposerPackage('symfony/ux-icons'));

var_dump($result['metadata']);
