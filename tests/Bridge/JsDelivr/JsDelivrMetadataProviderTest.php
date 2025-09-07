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
use PackApi\Bridge\JsDelivr\JsDelivrMetadataProvider;
use PackApi\Model\Metadata;
use PackApi\Package\Package;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(JsDelivrMetadataProvider::class)]
final class JsDelivrMetadataProviderTest extends TestCase
{
    private function makePackage(string $id): Package
    {
        return new class('name', $id) extends Package {};
    }

    public function testSupportsPrefixes(): void
    {
        $provider = new JsDelivrMetadataProvider(new JsDelivrApiClient(new MockHttpClient()));

        $this->assertTrue($provider->supports($this->makePackage('npm/pkg')));
        $this->assertTrue($provider->supports($this->makePackage('composer/pkg')));
        $this->assertFalse($provider->supports($this->makePackage('gh/pkg')));
    }

    public function testGetMetadataReturnsNullWhenApiReturnsNull(): void
    {
        $http = new MockHttpClient([
            new MockResponse('', ['http_code' => 404]),
        ]);
        $provider = new JsDelivrMetadataProvider(new JsDelivrApiClient($http));
        $package = $this->makePackage('npm/pkg');

        $this->assertNull($provider->getMetadata($package));
    }

    public function testGetMetadataBuildsModel(): void
    {
        $data = [
            'name' => 'pkg',
            'description' => 'desc',
            'license' => 'MIT',
            'repository' => 'https://repo',
        ];
        $http = new MockHttpClient([
            new MockResponse(json_encode($data, JSON_THROW_ON_ERROR), ['http_code' => 200]),
        ]);
        $provider = new JsDelivrMetadataProvider(new JsDelivrApiClient($http));
        $package = $this->makePackage('npm/pkg');

        $meta = $provider->getMetadata($package);

        $this->assertInstanceOf(Metadata::class, $meta);
        $this->assertSame('pkg', $meta->getName());
        $this->assertSame('desc', $meta->getDescription());
        $this->assertSame('MIT', $meta->getLicense());
        $this->assertSame('https://repo', $meta->getRepository());
    }

    public function testGetMetadataUsesPackageIdentifierWhenNameMissing(): void
    {
        $data = ['description' => 'only desc'];
        $http = new MockHttpClient([
            new MockResponse(json_encode($data, JSON_THROW_ON_ERROR), ['http_code' => 200]),
        ]);
        $provider = new JsDelivrMetadataProvider(new JsDelivrApiClient($http));
        $package = $this->makePackage('npm/pkg');

        $meta = $provider->getMetadata($package);

        $this->assertInstanceOf(Metadata::class, $meta);
        $this->assertSame('npm/pkg', $meta->getName());
        $this->assertSame('only desc', $meta->getDescription());
    }
}
