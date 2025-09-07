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

namespace PackApi\Provider;

use PackApi\Model\Metadata;
use PackApi\Package\Package;

/**
 * Contract for providing package metadata (name, description, license, repository, etc.).
 */
interface MetadataProviderInterface
{
    /**
     * Whether this provider supports the given package type.
     */
    public function supports(Package $package): bool;

    /**
     * Retrieve metadata for the given package.
     *
     * @return Metadata|null returns null if not supported or no data available
     */
    public function getMetadata(Package $package): ?Metadata;
}
