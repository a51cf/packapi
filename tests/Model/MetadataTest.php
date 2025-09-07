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

namespace PackApi\Tests\Model;

use PackApi\Model\Metadata;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Metadata::class)]
final class MetadataTest extends TestCase
{
    public function testConstructorAndGettersWithAllValues(): void
    {
        $name = 'symfony/http-foundation';
        $description = 'Defines an object-oriented layer for the HTTP specification';
        $license = 'MIT';
        $repository = 'https://github.com/symfony/http-foundation';

        $metadata = new Metadata($name, $description, $license, $repository);

        $this->assertSame($name, $metadata->getName());
        $this->assertSame($description, $metadata->getDescription());
        $this->assertSame($license, $metadata->getLicense());
        $this->assertSame($repository, $metadata->getRepository());

        // Test public readonly properties
        $this->assertSame($name, $metadata->name);
        $this->assertSame($description, $metadata->description);
        $this->assertSame($license, $metadata->license);
        $this->assertSame($repository, $metadata->repository);
    }

    public function testConstructorWithNameOnly(): void
    {
        $name = 'lodash';

        $metadata = new Metadata($name);

        $this->assertSame($name, $metadata->getName());
        $this->assertNull($metadata->getDescription());
        $this->assertNull($metadata->getLicense());
        $this->assertNull($metadata->getRepository());

        // Test public readonly properties
        $this->assertSame($name, $metadata->name);
        $this->assertNull($metadata->description);
        $this->assertNull($metadata->license);
        $this->assertNull($metadata->repository);
    }

    public function testConstructorWithNameAndDescription(): void
    {
        $name = 'react';
        $description = 'A JavaScript library for building user interfaces';

        $metadata = new Metadata($name, $description);

        $this->assertSame($name, $metadata->getName());
        $this->assertSame($description, $metadata->getDescription());
        $this->assertNull($metadata->getLicense());
        $this->assertNull($metadata->getRepository());
    }

    public function testConstructorWithNameDescriptionAndLicense(): void
    {
        $name = 'vue';
        $description = 'The Progressive JavaScript Framework';
        $license = 'MIT';

        $metadata = new Metadata($name, $description, $license);

        $this->assertSame($name, $metadata->getName());
        $this->assertSame($description, $metadata->getDescription());
        $this->assertSame($license, $metadata->getLicense());
        $this->assertNull($metadata->getRepository());
    }

    public function testConstructorWithEmptyStrings(): void
    {
        $name = 'test-package';
        $description = '';
        $license = '';
        $repository = '';

        $metadata = new Metadata($name, $description, $license, $repository);

        $this->assertSame($name, $metadata->getName());
        $this->assertSame('', $metadata->getDescription());
        $this->assertSame('', $metadata->getLicense());
        $this->assertSame('', $metadata->getRepository());
    }

    public function testConstructorWithScopedPackageName(): void
    {
        $name = '@types/node';
        $description = 'TypeScript definitions for Node.js';
        $license = 'MIT';
        $repository = 'https://github.com/DefinitelyTyped/DefinitelyTyped';

        $metadata = new Metadata($name, $description, $license, $repository);

        $this->assertSame($name, $metadata->getName());
        $this->assertSame($description, $metadata->getDescription());
        $this->assertSame($license, $metadata->getLicense());
        $this->assertSame($repository, $metadata->getRepository());
    }

    public function testConstructorWithComplexLicense(): void
    {
        $name = 'test-package';
        $license = 'Apache-2.0 OR MIT';

        $metadata = new Metadata($name, null, $license);

        $this->assertSame($name, $metadata->getName());
        $this->assertNull($metadata->getDescription());
        $this->assertSame($license, $metadata->getLicense());
        $this->assertNull($metadata->getRepository());
    }

    public function testConstructorWithRepositoryUrlVariations(): void
    {
        $testCases = [
            'https://github.com/user/repo',
            'git@github.com:user/repo.git',
            'https://gitlab.com/user/repo',
            'https://bitbucket.org/user/repo',
        ];

        foreach ($testCases as $repository) {
            $metadata = new Metadata('test-package', null, null, $repository);

            $this->assertSame('test-package', $metadata->getName());
            $this->assertSame($repository, $metadata->getRepository());
        }
    }

    public function testConstructorWithLongDescription(): void
    {
        $name = 'test-package';
        $description = str_repeat('This is a very long description. ', 50);

        $metadata = new Metadata($name, $description);

        $this->assertSame($name, $metadata->getName());
        $this->assertSame($description, $metadata->getDescription());
        $this->assertGreaterThan(1000, strlen($metadata->getDescription()));
    }

    public function testConstructorWithSpecialCharacters(): void
    {
        $name = 'special-chars-package';
        $description = 'Package with special chars: äöüß@#$%^&*()[]{}|\\:";\'<>?,.';
        $license = 'GPL-3.0+';
        $repository = 'https://example.com/user/repo?tab=readme&special=chars';

        $metadata = new Metadata($name, $description, $license, $repository);

        $this->assertSame($name, $metadata->getName());
        $this->assertSame($description, $metadata->getDescription());
        $this->assertSame($license, $metadata->getLicense());
        $this->assertSame($repository, $metadata->getRepository());
    }
}
