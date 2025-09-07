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

use PackApi\Model\Metadata;
use PackApi\Package\Package;

interface MetadataInspectorInterface
{
    /**
     * Get metadata for the given package from the first supporting provider.
     *
     * @return Metadata|null returns null if not supported or no data available
     */
    public function getMetadata(Package $package): ?Metadata;
}
