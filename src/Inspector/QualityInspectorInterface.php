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

namespace PackApi\Inspector;

use PackApi\Model\QualityScore;
use PackApi\Package\Package;

/**
 * Contract for computing a package quality score.
 */
interface QualityInspectorInterface
{
    /**
     * Get a quality score for the given package.
     *
     * @return QualityScore|null returns null if not supported or no data available
     */
    public function getQualityScore(Package $package): ?QualityScore;
}
