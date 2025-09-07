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
final class EnvAuthenticationManagerWithTokenTest extends TestCase
{
    public function testReturnsTokenIfEnvVariableIsSet(): void
    {
        putenv('GITHUB_TOKEN=test_token');
        $manager = new EnvAuthenticationManager('GITHUB_TOKEN');
        $this->assertSame('test_token', $manager->getGitHubToken());
        putenv('GITHUB_TOKEN'); // unset
    }
}
