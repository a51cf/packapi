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

use PackApi\Model\QualityScore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(QualityScore::class)]
final class QualityScoreTest extends TestCase
{
    public function testConstructorAndGettersWithAllValues(): void
    {
        $score = 85;
        $criteria = [
            'hasReadme' => true,
            'hasLicense' => true,
            'hasTests' => true,
            'hasDocumentation' => false,
            'codeComplexity' => 'low',
        ];
        $grade = 'B+';
        $comment = 'Good quality package with room for improvement in documentation';

        $qualityScore = new QualityScore($score, $criteria, $grade, $comment);

        $this->assertSame($score, $qualityScore->getScore());
        $this->assertSame($criteria, $qualityScore->getCriteria());
        $this->assertSame($grade, $qualityScore->getGrade());
        $this->assertSame($comment, $qualityScore->getComment());

        // Test public readonly properties
        $this->assertSame($score, $qualityScore->score);
        $this->assertSame($criteria, $qualityScore->criteria);
        $this->assertSame($grade, $qualityScore->grade);
        $this->assertSame($comment, $qualityScore->comment);
    }

    public function testConstructorWithScoreOnly(): void
    {
        $score = 75;

        $qualityScore = new QualityScore($score);

        $this->assertSame($score, $qualityScore->getScore());
        $this->assertSame([], $qualityScore->getCriteria());
        $this->assertNull($qualityScore->getGrade());
        $this->assertNull($qualityScore->getComment());

        // Test public readonly properties
        $this->assertSame($score, $qualityScore->score);
        $this->assertSame([], $qualityScore->criteria);
        $this->assertNull($qualityScore->grade);
        $this->assertNull($qualityScore->comment);
    }

    public function testConstructorWithScoreAndCriteria(): void
    {
        $score = 92;
        $criteria = [
            'hasReadme' => true,
            'hasLicense' => true,
            'hasTests' => true,
            'hasDocumentation' => true,
            'hasTypeDefinitions' => true,
            'maintainedRecently' => true,
        ];

        $qualityScore = new QualityScore($score, $criteria);

        $this->assertSame($score, $qualityScore->getScore());
        $this->assertSame($criteria, $qualityScore->getCriteria());
        $this->assertNull($qualityScore->getGrade());
        $this->assertNull($qualityScore->getComment());
    }

    public function testConstructorWithPerfectScore(): void
    {
        $score = 100;
        $criteria = [
            'hasReadme' => true,
            'hasLicense' => true,
            'hasTests' => true,
            'hasDocumentation' => true,
            'codeQuality' => 'excellent',
            'security' => 'no_vulnerabilities',
        ];
        $grade = 'A+';
        $comment = 'Excellent package with all quality criteria met';

        $qualityScore = new QualityScore($score, $criteria, $grade, $comment);

        $this->assertSame(100, $qualityScore->getScore());
        $this->assertSame($grade, $qualityScore->getGrade());
        $this->assertSame($comment, $qualityScore->getComment());
        $this->assertTrue($qualityScore->getCriteria()['hasReadme']);
        $this->assertTrue($qualityScore->getCriteria()['hasLicense']);
        $this->assertTrue($qualityScore->getCriteria()['hasTests']);
        $this->assertTrue($qualityScore->getCriteria()['hasDocumentation']);
    }

    public function testConstructorWithMinimumScore(): void
    {
        $score = 0;
        $criteria = [
            'hasReadme' => false,
            'hasLicense' => false,
            'hasTests' => false,
            'hasDocumentation' => false,
            'codeQuality' => 'poor',
        ];
        $grade = 'F';
        $comment = 'Package needs significant improvements';

        $qualityScore = new QualityScore($score, $criteria, $grade, $comment);

        $this->assertSame(0, $qualityScore->getScore());
        $this->assertSame($grade, $qualityScore->getGrade());
        $this->assertSame($comment, $qualityScore->getComment());
        $this->assertFalse($qualityScore->getCriteria()['hasReadme']);
        $this->assertFalse($qualityScore->getCriteria()['hasLicense']);
        $this->assertFalse($qualityScore->getCriteria()['hasTests']);
        $this->assertFalse($qualityScore->getCriteria()['hasDocumentation']);
    }

    public function testConstructorWithEmptyCriteria(): void
    {
        $score = 50;
        $criteria = [];

        $qualityScore = new QualityScore($score, $criteria);

        $this->assertSame($score, $qualityScore->getScore());
        $this->assertSame([], $qualityScore->getCriteria());
        $this->assertIsArray($qualityScore->getCriteria());
        $this->assertCount(0, $qualityScore->getCriteria());
    }

    public function testConstructorWithNumericCriteria(): void
    {
        $score = 78;
        $criteria = [
            'testCoverage' => 85.5,
            'dependencies' => 12,
            'vulnerabilities' => 0,
            'lastUpdate' => 30, // days ago
        ];

        $qualityScore = new QualityScore($score, $criteria);

        $this->assertSame($score, $qualityScore->getScore());
        $this->assertSame($criteria, $qualityScore->getCriteria());
        $this->assertSame(85.5, $qualityScore->getCriteria()['testCoverage']);
        $this->assertSame(12, $qualityScore->getCriteria()['dependencies']);
        $this->assertSame(0, $qualityScore->getCriteria()['vulnerabilities']);
        $this->assertSame(30, $qualityScore->getCriteria()['lastUpdate']);
    }

    public function testConstructorWithStringCriteria(): void
    {
        $score = 67;
        $criteria = [
            'license' => 'MIT',
            'language' => 'TypeScript',
            'framework' => 'React',
            'buildTool' => 'Webpack',
        ];

        $qualityScore = new QualityScore($score, $criteria);

        $this->assertSame($score, $qualityScore->getScore());
        $this->assertSame($criteria, $qualityScore->getCriteria());
        $this->assertSame('MIT', $qualityScore->getCriteria()['license']);
        $this->assertSame('TypeScript', $qualityScore->getCriteria()['language']);
        $this->assertSame('React', $qualityScore->getCriteria()['framework']);
        $this->assertSame('Webpack', $qualityScore->getCriteria()['buildTool']);
    }

    public function testConstructorWithComplexGrade(): void
    {
        $testCases = [
            ['score' => 95, 'grade' => 'A+'],
            ['score' => 88, 'grade' => 'A'],
            ['score' => 82, 'grade' => 'A-'],
            ['score' => 78, 'grade' => 'B+'],
            ['score' => 72, 'grade' => 'B'],
            ['score' => 68, 'grade' => 'B-'],
            ['score' => 62, 'grade' => 'C+'],
            ['score' => 55, 'grade' => 'C'],
            ['score' => 48, 'grade' => 'D'],
            ['score' => 35, 'grade' => 'F'],
        ];

        foreach ($testCases as $testCase) {
            $qualityScore = new QualityScore($testCase['score'], [], $testCase['grade']);

            $this->assertSame($testCase['score'], $qualityScore->getScore());
            $this->assertSame($testCase['grade'], $qualityScore->getGrade());
        }
    }

    public function testConstructorWithLongComment(): void
    {
        $score = 73;
        $comment = 'This package shows good potential but has several areas for improvement. '.
                   'The codebase is well-structured and follows modern best practices. '.
                   'However, test coverage could be improved, and documentation needs updates. '.
                   'Security scan found no major vulnerabilities. '.
                   'Overall, a solid package that would benefit from continued maintenance.';

        $qualityScore = new QualityScore($score, [], null, $comment);

        $this->assertSame($score, $qualityScore->getScore());
        $this->assertSame($comment, $qualityScore->getComment());
        $this->assertGreaterThan(200, strlen($qualityScore->getComment()));
    }

    public function testConstructorWithEmptyStringValues(): void
    {
        $score = 60;
        $grade = '';
        $comment = '';

        $qualityScore = new QualityScore($score, [], $grade, $comment);

        $this->assertSame($score, $qualityScore->getScore());
        $this->assertSame('', $qualityScore->getGrade());
        $this->assertSame('', $qualityScore->getComment());
    }
}
