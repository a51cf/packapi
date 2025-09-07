<?php

$licence = <<<'EOF'
This file is part of the smnandre/packapi package.

(c) Simon Andre <smn.andre@gmail.com>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude([
        'tests',
    ])
;

return (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS' => true,
        '@Symfony' => true,
        'declare_strict_types' => true,
        'strict_param' => true,
        'header_comment' => ['header' => $licence],
    ])
;
