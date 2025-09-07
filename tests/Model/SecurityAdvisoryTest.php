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

use PackApi\Model\SecurityAdvisory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SecurityAdvisory::class)]
final class SecurityAdvisoryTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $id = 'CVE-2023-12345';
        $title = 'Cross-Site Scripting vulnerability in user input handling';
        $severity = 'HIGH';
        $link = 'https://github.com/advisories/GHSA-xxxx-yyyy-zzzz';

        $advisory = new SecurityAdvisory($id, $title, $severity, $link);

        $this->assertSame($id, $advisory->getId());
        $this->assertSame($title, $advisory->getTitle());
        $this->assertSame($severity, $advisory->getSeverity());
        $this->assertSame($link, $advisory->getLink());

        // Test public readonly properties
        $this->assertSame($id, $advisory->id);
        $this->assertSame($title, $advisory->title);
        $this->assertSame($severity, $advisory->severity);
        $this->assertSame($link, $advisory->link);
    }

    public function testConstructorWithDifferentSeverityLevels(): void
    {
        $testCases = [
            ['severity' => 'CRITICAL', 'id' => 'CVE-2023-0001'],
            ['severity' => 'HIGH', 'id' => 'CVE-2023-0002'],
            ['severity' => 'MEDIUM', 'id' => 'CVE-2023-0003'],
            ['severity' => 'LOW', 'id' => 'CVE-2023-0004'],
            ['severity' => 'INFO', 'id' => 'CVE-2023-0005'],
        ];

        foreach ($testCases as $testCase) {
            $advisory = new SecurityAdvisory(
                $testCase['id'],
                'Test vulnerability',
                $testCase['severity'],
                'https://example.com/advisory'
            );

            $this->assertSame($testCase['id'], $advisory->getId());
            $this->assertSame($testCase['severity'], $advisory->getSeverity());
        }
    }

    public function testConstructorWithDifferentIdFormats(): void
    {
        $testCases = [
            'CVE-2023-12345',
            'GHSA-xxxx-yyyy-zzzz',
            'PYSEC-2023-123',
            'RUSTSEC-2023-0001',
            'GO-2023-1234',
            'NPM-2023-0001',
            'COMPOSER-2023-001',
        ];

        foreach ($testCases as $id) {
            $advisory = new SecurityAdvisory(
                $id,
                'Test vulnerability for '.$id,
                'MEDIUM',
                'https://example.com/advisory/'.$id
            );

            $this->assertSame($id, $advisory->getId());
            $this->assertSame('Test vulnerability for '.$id, $advisory->getTitle());
        }
    }

    public function testConstructorWithLongTitle(): void
    {
        $id = 'CVE-2023-99999';
        $title = 'This is a very long security advisory title that describes a complex vulnerability involving multiple components and attack vectors that could potentially lead to remote code execution, data exfiltration, and complete system compromise when exploited by malicious actors under specific conditions.';
        $severity = 'CRITICAL';
        $link = 'https://nvd.nist.gov/vuln/detail/CVE-2023-99999';

        $advisory = new SecurityAdvisory($id, $title, $severity, $link);

        $this->assertSame($id, $advisory->getId());
        $this->assertSame($title, $advisory->getTitle());
        $this->assertSame($severity, $advisory->getSeverity());
        $this->assertSame($link, $advisory->getLink());
        $this->assertGreaterThan(200, strlen($advisory->getTitle()));
    }

    public function testConstructorWithDifferentLinkTypes(): void
    {
        $testCases = [
            'https://github.com/advisories/GHSA-xxxx-yyyy-zzzz',
            'https://nvd.nist.gov/vuln/detail/CVE-2023-12345',
            'https://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2023-12345',
            'https://security.snyk.io/vuln/SNYK-JS-PACKAGE-12345',
            'https://rustsec.org/advisories/RUSTSEC-2023-0001.html',
            'https://osv.dev/vulnerability/PYSEC-2023-123',
        ];

        foreach ($testCases as $link) {
            $advisory = new SecurityAdvisory(
                'TEST-001',
                'Test vulnerability',
                'MEDIUM',
                $link
            );

            $this->assertSame($link, $advisory->getLink());
            $this->assertStringStartsWith('https://', $advisory->getLink());
        }
    }

    public function testConstructorWithSpecialCharacters(): void
    {
        $id = 'SPECIAL-2023-001';
        $title = 'Vulnerability with special chars: <script>alert("XSS")</script> & "quotes" & \'apostrophes\'';
        $severity = 'HIGH';
        $link = 'https://example.com/advisory?id=special&param=value';

        $advisory = new SecurityAdvisory($id, $title, $severity, $link);

        $this->assertSame($id, $advisory->getId());
        $this->assertSame($title, $advisory->getTitle());
        $this->assertSame($severity, $advisory->getSeverity());
        $this->assertSame($link, $advisory->getLink());
        $this->assertStringContainsString('<script>', $advisory->getTitle());
        $this->assertStringContainsString('"quotes"', $advisory->getTitle());
        $this->assertStringContainsString("'apostrophes'", $advisory->getTitle());
    }

    public function testConstructorWithEmptyStrings(): void
    {
        $advisory = new SecurityAdvisory('', '', '', '');

        $this->assertSame('', $advisory->getId());
        $this->assertSame('', $advisory->getTitle());
        $this->assertSame('', $advisory->getSeverity());
        $this->assertSame('', $advisory->getLink());
    }

    public function testConstructorWithUnicodeCharacters(): void
    {
        $id = 'UNICODE-2023-001';
        $title = 'Vulnerability affecting files with unicode names: 文档.txt, resumé.pdf, naïve.js';
        $severity = 'MEDIUM';
        $link = 'https://example.com/advisory/unicode';

        $advisory = new SecurityAdvisory($id, $title, $severity, $link);

        $this->assertSame($id, $advisory->getId());
        $this->assertSame($title, $advisory->getTitle());
        $this->assertSame($severity, $advisory->getSeverity());
        $this->assertSame($link, $advisory->getLink());
        $this->assertStringContainsString('文档', $advisory->getTitle());
        $this->assertStringContainsString('resumé', $advisory->getTitle());
        $this->assertStringContainsString('naïve', $advisory->getTitle());
    }

    public function testConstructorWithNumericStrings(): void
    {
        $id = '2023001';
        $title = '123 SQL Injection vulnerability in version 4.5.6';
        $severity = '7.5'; // CVSS score as string
        $link = 'https://example.com/advisory/123';

        $advisory = new SecurityAdvisory($id, $title, $severity, $link);

        $this->assertSame($id, $advisory->getId());
        $this->assertSame($title, $advisory->getTitle());
        $this->assertSame($severity, $advisory->getSeverity());
        $this->assertSame($link, $advisory->getLink());
    }

    public function testGetters(): void
    {
        $advisory = new SecurityAdvisory('ID', 'Title', 'Severity', 'Link');

        $this->assertSame('ID', $advisory->getId());
        $this->assertSame('Title', $advisory->getTitle());
        $this->assertSame('Severity', $advisory->getSeverity());
        $this->assertSame('Link', $advisory->getLink());
    }
}
