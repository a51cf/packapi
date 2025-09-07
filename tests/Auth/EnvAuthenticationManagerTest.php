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

namespace PackApi\Tests\Auth;

use PackApi\Auth\EnvAuthenticationManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EnvAuthenticationManager::class)]
final class EnvAuthenticationManagerTest extends TestCase
{
    public function testReturnsNullIfEnvVariableIsNotSet(): void
    {
        putenv('NON_EXISTENT_TOKEN'); // Ensure it's not set
        $manager = new EnvAuthenticationManager('NON_EXISTENT_TOKEN');
        $this->assertNull($manager->getGitHubToken());
    }
}
