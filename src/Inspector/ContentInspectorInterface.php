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

use PackApi\Model\ContentOverview;
use PackApi\Package\Package;

/**
 * Contract for content inspection of a package's distributed files.
 */
interface ContentInspectorInterface
{
    /**
     * Get an overview of the distributed content for the given package.
     *
     * @return ContentOverview|null returns null if not supported or no data available
     */
    public function getContentOverview(Package $package): ?ContentOverview;
}
