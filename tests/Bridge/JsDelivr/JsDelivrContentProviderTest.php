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

namespace PackApi\Tests\Bridge\JsDelivr;

use PackApi\Bridge\JsDelivr\JsDelivrApiClient;
use PackApi\Bridge\JsDelivr\JsDelivrContentProvider;
use PackApi\Model\ContentOverview;
use PackApi\Model\File;
use PackApi\Package\Package;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(JsDelivrContentProvider::class)]
final class JsDelivrContentProviderTest extends TestCase
{
    private JsDelivrContentProvider $provider;

    private function makePackage(string $id): Package
    {
        return new class('name', $id) extends Package {};
    }

    public function testSupportsRecognizesPrefixes(): void
    {
        $client = new JsDelivrApiClient(new MockHttpClient());
        $this->provider = new JsDelivrContentProvider($client);

        $npm = $this->makePackage('npm/test');
        $composer = $this->makePackage('composer/test');
        $other = $this->makePackage('gh/test');

        $this->assertTrue($this->provider->supports($npm));
        $this->assertTrue($this->provider->supports($composer));
        $this->assertFalse($this->provider->supports($other));
    }

    public function testGetContentOverviewReturnsNullWhenApiReturnsNull(): void
    {
        $http = new MockHttpClient([
            new MockResponse('', ['http_code' => 404]),
        ]);
        $client = new JsDelivrApiClient($http);
        $this->provider = new JsDelivrContentProvider($client);

        $package = $this->makePackage('npm/test');

        $this->assertNull($this->provider->getContentOverview($package));
    }

    public function testGetContentOverviewBuildsModel(): void
    {
        $files = [
            ['name' => 'README.md', 'size' => 100, 'time' => '2024-01-01T00:00:00Z'],
            ['name' => 'LICENSE', 'size' => 50],
            ['name' => 'test/example.php', 'size' => 20],
            ['name' => '.gitattributes', 'size' => 1],
            ['name' => '.gitignore', 'size' => 1],
            ['name' => 'src/index.js', 'size' => 100],
        ];
        $http = new MockHttpClient([
            new MockResponse(json_encode(['files' => $files], JSON_THROW_ON_ERROR), ['http_code' => 200]),
        ]);
        $client = new JsDelivrApiClient($http);
        $this->provider = new JsDelivrContentProvider($client);
        $package = $this->makePackage('npm/pkg');

        $overview = $this->provider->getContentOverview($package);

        $this->assertInstanceOf(ContentOverview::class, $overview);
        $this->assertSame(6, $overview->getFileCount());
        $this->assertSame(272, $overview->getTotalSize());
        $this->assertTrue($overview->hasReadme());
        $this->assertTrue($overview->hasLicense());
        $this->assertTrue($overview->hasTests());
        $this->assertTrue($overview->hasGitattributes());
        $this->assertTrue($overview->hasGitignore());
        $this->assertSame(['test/example.php'], $overview->getIgnoredFiles());

        $paths = array_map(fn (File $f) => $f->getPath(), $overview->getFiles());
        $this->assertContains('README.md', $paths);
        $this->assertContains('src/index.js', $paths);
    }
}
