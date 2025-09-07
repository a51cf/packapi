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

use PackApi\Model\QualityScore;
use PackApi\Package\Package;
use PackApi\Provider\ContentProviderInterface;

final class QualityInspector implements QualityInspectorInterface
{
    public function __construct(
        private ContentInspectorInterface $contentInspector,
        private MetadataInspectorInterface $metadataInspector,
        private ?ContentProviderInterface $bestPracticeProvider = null,
    ) {
    }

    public function getQualityScore(Package $package): ?QualityScore
    {
        $content = $this->contentInspector->getContentOverview($package);
        $meta = $this->metadataInspector->getMetadata($package);
        $bestPractices = $this->bestPracticeProvider?->getContentOverview($package);
        if (!$content || !$meta) {
            return null;
        }

        $criteria = [
            'hasReadme' => $content->hasReadme,
            'hasLicense' => $content->hasLicense,
            'hasTests' => $content->hasTests,
            'hasDescription' => !empty($meta->description),
            'hasRepository' => !empty($meta->repository),
            'ignoredFiles' => count($content->ignoredFiles),
        ];
        if ($bestPractices) {
            $criteria['hasGitattributes'] = (bool) array_filter($bestPractices->ignoredFiles, fn ($f) => '.gitattributes' === $f);
            $criteria['hasGitignore'] = (bool) array_filter($bestPractices->ignoredFiles, fn ($f) => '.gitignore' === $f);
        }

        // Improved scoring: each criterion is worth 15 points, minus penalty for ignored files
        $score = 0;
        $score += $criteria['hasReadme'] ? 15 : 0;
        $score += $criteria['hasLicense'] ? 15 : 0;
        $score += $criteria['hasTests'] ? 15 : 0;
        $score += $criteria['hasDescription'] ? 10 : 0;
        $score += $criteria['hasRepository'] ? 10 : 0;
        $score += ($criteria['hasGitattributes'] ?? false) ? 10 : 0;
        $score += ($criteria['hasGitignore'] ?? false) ? 10 : 0;
        $score -= min($criteria['ignoredFiles'] * 2, 15); // up to -15 for ignored files
        $score = max(0, min(100, $score));

        $grade = match (true) {
            $score >= 80 => 'A',
            $score >= 65 => 'B',
            $score >= 50 => 'C',
            $score >= 35 => 'D',
            default => 'F',
        };

        $comment = match ($grade) {
            'A' => 'Excellent package hygiene and best practices.',
            'B' => 'Good, but could be improved (see best practices).',
            'C' => 'Average quality, some best practices missing.',
            'D' => 'Below average, missing key best practices.',
            default => 'Poor quality, needs significant improvement.',
        };

        return new QualityScore($score, $criteria, $grade, $comment);
    }
}
