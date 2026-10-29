<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo-meta', [
        'title' => $course->title.' | think.er HUB',
        'description' => $course->overview ?: ($course->description ?: 'Tutor-led, practical course on think.er HUB for learners ready to upskill.'),
        'keywords' => strtolower(($course->code ? $course->code.', ' : '').'thinker hub, digital skills, practical training'),
        'type' => 'article',
    ])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.pwa-register')
    <style>
        .hub-fee-row {
            transition: all 0.2s ease-in-out;
        }
    </style>
</head>
<body class="public-layout hub-public bg-[#f8fcf9] text-slate-900 font-sans antialiased" x-data="{ mobileMenu: false }">

    @include('partials.public-header')

    <main>
        @php
            $toLines = static function (mixed $value): array {
                if ($value === null) {
                    return [];
                }

                if (is_array($value)) {
                    return array_values(array_filter(array_map(static fn (mixed $line): string => trim((string) $line), $value)));
                }

                $lines = preg_split('/\r\n|\r|\n/', (string) $value) ?: [];

                return array_values(array_filter(array_map(static fn (string $line): string => trim($line), $lines)));
            };

            $parseStructured = static function (mixed $value) {
                if (is_array($value)) {
                    return $value;
                }

                if (! is_string($value)) {
                    return $value;
                }

                $trimmed = trim($value);

                if ($trimmed === '') {
                    return '';
                }

                $isJsonObject = str_starts_with($trimmed, '{') && str_ends_with($trimmed, '}');
                $isJsonArray = str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']');

                if (! $isJsonObject && ! $isJsonArray) {
                    return $value;
                }

                $decoded = json_decode($trimmed, true);

                return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
            };

            $normalizeFeeSectionKey = static function (string $key): string {
                $normalized = strtolower(trim(str_replace(['-', ' '], '_', $key)));

                if (in_array($normalized, ['one_on_one', 'one2one', 'one_to_one', 'private', 'private_class'], true)) {
                    return 'one_on_one';
                }

                if (in_array($normalized, ['self_paced', 'selfpaced', 'self_study', 'self_learning', 'asynchronous'], true)) {
                    return 'self_paced';
                }

                if (in_array($normalized, ['group', 'group_class', 'group_classes', 'class_group'], true)) {
                    return 'group';
                }

                return $normalized !== '' ? $normalized : 'fees';
            };

            $detectFeeMode = static function (string $value): string {
                $text = strtolower($value);

                if (preg_match('/one\s*[-:]?\s*on\s*[-:]?\s*one|1\s*[:x]\s*1|private/', $text) === 1) {
                    return 'one_on_one';
                }

                if (str_contains($text, 'self') || str_contains($text, 'paced')) {
                    return 'self_paced';
                }

                if (str_contains($text, 'group')) {
                    return 'group';
                }

                return 'fees';
            };

            $cleanFeeToken = static function (string $value): string {
                return trim((string) preg_replace('/\b(one\s*[-:]?\s*on\s*[-:]?\s*one|1\s*[:x]\s*1|private|group)\b\s*[:\-]?\s*/i', '', preg_replace('/\s{2,}/', ' ', $value) ?? $value));
            };

            $cleanFeeLevel = static function (string $value) use ($cleanFeeToken): string {
                return $cleanFeeToken(trim((string) preg_replace('/^level\s*[:\-]\s*/i', '', $value)));
            };

            $extractFeeModeAndRemainder = static function (string $line) use ($detectFeeMode): array {
                $mode = $detectFeeMode($line);
                $remainder = trim((string) preg_replace('/^(one\s*[-\s:]?\s*on\s*[-\s:]?\s*one|1\s*[:x]\s*1|private|group)\s*(?:[:\-|]\s*)?/i', '', $line));

                return [
                    'mode' => $mode,
                    'remainder' => $remainder !== '' ? $remainder : trim($line),
                ];
            };

            $parseFeeRows = static function (mixed $rows, ?string $forcedMode = null) use ($toLines, $extractFeeModeAndRemainder, $cleanFeeLevel, $cleanFeeToken, $normalizeFeeSectionKey): array {
                $sourceRows = is_array($rows) ? $rows : $toLines($rows);
                $normalized = [];

                foreach ($sourceRows as $entry) {
                    if (is_array($entry)) {
                        $mode = $normalizeFeeSectionKey((string) ($forcedMode ?? ($entry['mode'] ?? $entry['type'] ?? 'fees')));
                        $level = $cleanFeeLevel((string) ($entry['level'] ?? ''));
                        $amount = $cleanFeeToken((string) ($entry['amount'] ?? ''));
                        $duration = trim((string) ($entry['duration'] ?? ''));

                        if ($level !== '' || $amount !== '' || $duration !== '') {
                            $normalized[] = [
                                'level' => $level,
                                'amount' => $amount,
                                'duration' => $duration,
                                'mode' => $mode,
                            ];
                        }

                        continue;
                    }

                    $line = trim((string) $entry);

                    if ($line === '') {
                        continue;
                    }

                    $extracted = $extractFeeModeAndRemainder($line);
                    $mode = $normalizeFeeSectionKey((string) ($forcedMode ?? $extracted['mode']));
                    $normalizedLine = (string) $extracted['remainder'];
                    $compactLine = trim((string) preg_replace('/\s+/', ' ', $normalizedLine));

                    if (preg_match_all('/\b(Beginner|Intermediate|Advanced)\b\s*[:\-]\s*([^()]+?)\s*(?:\(([^)]+)\))?(?=\s*(?:Beginner|Intermediate|Advanced)\s*[:\-]|$)/i', $compactLine, $matches, PREG_SET_ORDER) > 0) {
                        foreach ($matches as $match) {
                            $normalized[] = [
                                'level' => $cleanFeeLevel((string) ($match[1] ?? '')),
                                'amount' => $cleanFeeToken((string) ($match[2] ?? '')),
                                'duration' => trim((string) ($match[3] ?? '')),
                                'mode' => $mode,
                            ];
                        }

                        continue;
                    }

                    if (preg_match('/^([^:()|]+?)\s*:\s*([^()]+?)\s*(?:\(([^)]+)\))?$/', $normalizedLine, $matches) === 1) {
                        $normalized[] = [
                            'level' => $cleanFeeLevel((string) ($matches[1] ?? '')),
                            'amount' => $cleanFeeToken((string) ($matches[2] ?? '')),
                            'duration' => trim($matches[3] ?? ''),
                            'mode' => $mode,
                        ];

                        continue;
                    }

                    if (preg_match('/^(.+?)\s+-\s+([^()]+?)\s*(?:\(([^)]+)\))?$/', $normalizedLine, $matches) === 1) {
                        $normalized[] = [
                            'level' => $cleanFeeLevel((string) ($matches[1] ?? '')),
                            'amount' => $cleanFeeToken((string) ($matches[2] ?? '')),
                            'duration' => trim((string) ($matches[3] ?? '')),
                            'mode' => $mode,
                        ];

                        continue;
                    }

                    $parts = array_values(array_filter(array_map('trim', explode('|', $normalizedLine))));

                    if (count($parts) >= 2) {
                        $normalized[] = [
                            'level' => $cleanFeeLevel((string) ($parts[0] ?? '')),
                            'amount' => $cleanFeeToken((string) ($parts[1] ?? '')),
                            'duration' => $parts[2] ?? '',
                            'mode' => $mode,
                        ];

                        continue;
                    }

                    $normalized[] = [
                        'level' => $cleanFeeLevel($normalizedLine),
                        'amount' => '',
                        'duration' => '',
                        'mode' => $mode,
                    ];
                }

                return $normalized;
            };

            $rawFees = $parseStructured($course->fees);
            $feeSections = [];

            if (is_array($rawFees) && ! array_is_list($rawFees)) {
                foreach ($rawFees as $key => $rows) {
                    $normalizedKey = $normalizeFeeSectionKey((string) $key);
                    $label = match ($key) {
                        'one_on_one' => 'One-on-One',
                        'group' => 'Group',
                        'self_paced' => 'Self-Paced',
                        default => ucwords(str_replace('_', ' ', (string) $key)),
                    };

                    $badge = match ($key) {
                        'one_on_one' => '1:1 Focus',
                        'group' => 'Best Value',
                        'self_paced' => 'Flexible',
                        default => '',
                    };

                    $parsedRows = $parseFeeRows($rows, $normalizedKey);

                    if ($parsedRows !== []) {
                        $feeSections[] = [
                            'key' => $normalizedKey,
                            'label' => $label,
                            'badge' => $badge,
                            'rows' => $parsedRows,
                        ];
                    }
                }
            } else {
                $fallbackRows = $parseFeeRows($rawFees);

                if ($fallbackRows !== []) {
                    $groupedRows = [
                        'one_on_one' => array_values(array_filter($fallbackRows, static fn (array $row): bool => ($row['mode'] ?? 'fees') === 'one_on_one')),
                        'group' => array_values(array_filter($fallbackRows, static fn (array $row): bool => ($row['mode'] ?? 'fees') === 'group')),
                        'self_paced' => array_values(array_filter($fallbackRows, static fn (array $row): bool => ($row['mode'] ?? 'fees') === 'self_paced')),
                        'fees' => array_values(array_filter($fallbackRows, static fn (array $row): bool => ! in_array(($row['mode'] ?? 'fees'), ['one_on_one', 'group', 'self_paced'], true))),
                    ];

                    foreach (['one_on_one', 'group', 'self_paced', 'fees'] as $sectionKey) {
                        if ($groupedRows[$sectionKey] === []) {
                            continue;
                        }

                        $feeSections[] = [
                            'key' => $sectionKey,
                            'label' => match ($sectionKey) {
                                'one_on_one' => 'One-on-One',
                                'group' => 'Group',
                                'self_paced' => 'Self-Paced',
                                default => 'Fees',
                            },
                            'badge' => match ($sectionKey) {
                                'one_on_one' => '1:1 Focus',
                                'group' => 'Best Value',
                                'self_paced' => 'Flexible',
                                default => '',
                            },
                            'rows' => $groupedRows[$sectionKey],
                        ];
                    }
                }
            }

            $rawProgression = $parseStructured($course->level_progression);
            $progressionItems = [];

            if (is_array($rawProgression)) {
                foreach ($rawProgression as $entry) {
                    if (is_array($entry)) {
                        $level = trim((string) ($entry['level'] ?? ''));
                        $details = trim((string) ($entry['details'] ?? ''));

                        if ($level !== '' || $details !== '') {
                            $progressionItems[] = [
                                'level' => $level,
                                'details' => $details,
                            ];
                        }

                        continue;
                    }

                    $line = trim((string) $entry);

                    if ($line === '') {
                        continue;
                    }

                    $parts = explode(':', $line);

                    if (count($parts) > 1) {
                        $level = trim((string) array_shift($parts));
                        $details = trim(implode(':', $parts));
                    } else {
                        $level = $line;
                        $details = '';
                    }

                    $progressionItems[] = [
                        'level' => $level,
                        'details' => $details,
                    ];
                }
            } else {
                foreach ($toLines($rawProgression) as $line) {
                    $parts = explode(':', $line);

                    if (count($parts) > 1) {
                        $level = trim((string) array_shift($parts));
                        $details = trim(implode(':', $parts));
                    } else {
                        $level = trim($line);
                        $details = '';
                    }

                    if ($level !== '' || $details !== '') {
                        $progressionItems[] = [
                            'level' => $level,
                            'details' => $details,
                        ];
                    }
                }
            }

            $progressionCards = [];
            $progressionSourceText = trim(implode("\n", array_map(static fn (array $item): string => trim(($item['level'] !== '' ? $item['level'].': ' : '').($item['details'] ?? '')), $progressionItems)));
            $isLockedCourse = $course->is_open_enrollment === false;

            foreach (['Beginner', 'Intermediate', 'Advanced'] as $index => $levelName) {
                $matched = null;

                foreach ($progressionItems as $item) {
                    if (str_contains(strtolower((string) ($item['level'] ?? '')), strtolower($levelName)) && trim((string) ($item['details'] ?? '')) !== '') {
                        $matched = [
                            'level' => $levelName,
                            'details' => trim((string) $item['details']),
                        ];
                        break;
                    }
                }

                if ($matched === null && $progressionSourceText !== '') {
                    $nextLevel = ['Beginner', 'Intermediate', 'Advanced'][$index + 1] ?? null;
                    $pattern = $nextLevel !== null
                        ? '/'.preg_quote($levelName, '/').'\s*[:\-]\s*([\s\S]*?)(?='.preg_quote($nextLevel, '/').'\s*[:\-]|$)/i'
                        : '/'.preg_quote($levelName, '/').'\s*[:\-]\s*([\s\S]*?)$/i';

                    if (preg_match($pattern, $progressionSourceText, $match) === 1) {
                        $details = trim((string) ($match[1] ?? ''));

                        if ($details !== '') {
                            $matched = [
                                'level' => $levelName,
                                'details' => $details,
                            ];
                        }
                    }
                }

                if ($matched === null && count($progressionItems) === 1 && $index === 0) {
                    $fallback = trim(((string) ($progressionItems[0]['level'] ?? '') !== '' ? (string) $progressionItems[0]['level'].': ' : '').(string) ($progressionItems[0]['details'] ?? ''));

                    if ($fallback !== '') {
                        $matched = [
                            'level' => $levelName,
                            'details' => $fallback,
                        ];
                    }
                }

                if ($matched) {
                    $progressionCards[] = $matched;
                }
            }

            $progressionCards = array_values(array_filter(
                $progressionCards,
                static fn (array $item): bool => filled($item['level'] ?? null) && filled($item['details'] ?? null)
            ));

            $skillLevelGuidelines = [
                'Beginner' => [
                    'tier' => '1. Foundational Tier',
                    'badge' => 'Beginner',
                    'badgeColor' => 'emerald',
                    'target' => 'Absolute beginners with zero prior background or novices seeking structured, crystal-clear fundamentals.',
                    'focus' => 'Core fundamentals, foundational terminology, workspace and tool configuration, syntax rules, principles, and guided hands-on exercises.',
                    'milestones' => [
                        'Environment & tools configuration from ground zero',
                        'Core terminology, conceptual foundations, and key principles',
                        'Guided hands-on challenges and structured problem-solving',
                        'Confidence building with direct tutor mentorship',
                    ],
                    'trackType' => 'Standard Foundational Track',
                ],
                'Intermediate' => [
                    'tier' => '2. Practical Competency Tier',
                    'badge' => 'Intermediate',
                    'badgeColor' => 'blue',
                    'target' => 'Learners with foundational knowledge looking to build practical real-world capability and autonomous problem-solving.',
                    'focus' => 'Practical problem-solving, modular code organization, APIs, database integration, robust error handling, and independent mini-projects.',
                    'milestones' => [
                        'Clean, modular architecture & industry-standard conventions',
                        'API integration, data persistence & state handling',
                        'Independent troubleshooting, error handling & debugging',
                        'Completion of functional portfolio-ready mini-projects',
                    ],
                    'trackType' => 'Standard Practical Competency Track',
                ],
                'Advanced' => [
                    'tier' => '3. Professional Mastery Tier',
                    'badge' => 'Advanced',
                    'badgeColor' => 'purple',
                    'target' => 'Experienced practitioners, developers, and professionals aiming for senior-level depth, architectural mastery, and leadership.',
                    'focus' => 'Complex system architecture, performance optimization, security best practices, automated testing, CI/CD, and enterprise-grade delivery.',
                    'milestones' => [
                        'Scalable design patterns & complex data architecture',
                        'Production hardening, caching & latency reduction',
                        'Security compliance, authentication & audit logging',
                        'Automated testing & CI/CD deployment workflows',
                    ],
                    'trackType' => 'Standard Professional Mastery Track',
                ],
                'Advanced (Beginner to Advanced)' => [
                    'tier' => '4. Zero-to-Hero Track',
                    'badge' => 'Zero-to-Hero',
                    'badgeColor' => 'amber',
                    'target' => 'Ambitious learners wanting a comprehensive, unbroken progression journey starting at ground zero and graduating with advanced mastery.',
                    'focus' => 'Comprehensive end-to-end curriculum bridging foundational concepts, intermediate building blocks, and advanced system engineering in one cohesive journey.',
                    'milestones' => [
                        'Phase 1: Zero-friction fundamental setup & basic principles',
                        'Phase 2: Transition into modular development & database-backed projects',
                        'Phase 3: Deep dive into enterprise architecture, security & scaling',
                        'Capstone: Full end-to-end production application deployment',
                    ],
                    'trackType' => 'Comprehensive Journey (Zero-to-Hero)',
                ],
                'Advanced (Intermediate to Advanced)' => [
                    'tier' => '5. Specialist Acceleration Track',
                    'badge' => 'Specialist Acceleration',
                    'badgeColor' => 'indigo',
                    'target' => 'Proficient developers and practitioners who already possess strong fundamentals and want to fast-track directly into advanced specialisation.',
                    'focus' => 'Skips beginner setup to accelerate skilled learners into senior/lead developer capabilities, advanced design patterns, and system resilience.',
                    'milestones' => [
                        'Advanced design patterns & distributed architectures',
                        'Deep performance profiling, bottleneck detection & concurrency',
                        'Production resilience, microservices & automated pipelines',
                        'Industry-standard code reviews & architectural defense',
                    ],
                    'trackType' => 'Accelerated Specialist Track',
                ],
                'Intermediate (Beginner to Intermediate)' => [
                    'tier' => '6. Bridge Track',
                    'badge' => 'Bridge Track',
                    'badgeColor' => 'teal',
                    'target' => 'Novices who want more than basic syntax and need a guided bridge into self-sufficient, job-ready project creation.',
                    'focus' => 'Guides novices past the beginner plateau, grounding them in real-world habits, modular design, and confident autonomy.',
                    'milestones' => [
                        'Step-by-step bridging from elementary syntax to multi-file projects',
                        'Practical problem deconstruction and algorithmic thinking',
                        'Working with version control, third-party libraries & APIs',
                        'Building 2+ autonomous, working projects without guided templates',
                    ],
                    'trackType' => 'Guided Practical Bridge Track',
                ],
            ];

            $getLevelGuidelines = static function (string $rawLevelName) use ($skillLevelGuidelines, $progressionItems, $progressionCards, $course): array {
                $rawTrim = trim($rawLevelName);
                $matchedKey = null;

                foreach (array_keys($skillLevelGuidelines) as $key) {
                    if (strcasecmp($rawTrim, $key) === 0) {
                        $matchedKey = $key;
                        break;
                    }
                }

                if ($matchedKey === null) {
                    if (stripos($rawTrim, 'beginner to advanced') !== false) {
                        $matchedKey = 'Advanced (Beginner to Advanced)';
                    } elseif (stripos($rawTrim, 'intermediate to advanced') !== false) {
                        $matchedKey = 'Advanced (Intermediate to Advanced)';
                    } elseif (stripos($rawTrim, 'beginner to intermediate') !== false) {
                        $matchedKey = 'Intermediate (Beginner to Intermediate)';
                    } elseif (stripos($rawTrim, 'beginner') !== false) {
                        $matchedKey = 'Beginner';
                    } elseif (stripos($rawTrim, 'advanced') !== false) {
                        $matchedKey = 'Advanced';
                    } elseif (stripos($rawTrim, 'intermediate') !== false) {
                        $matchedKey = 'Intermediate';
                    }
                }

                $framework = $skillLevelGuidelines[$matchedKey] ?? [
                    'tier' => 'Skill Level Tier',
                    'badge' => $rawTrim,
                    'badgeColor' => 'teal',
                    'target' => 'Learners enrolled in the '.$rawTrim.' track.',
                    'focus' => 'Specialized competencies and progression outcomes for '.$rawTrim.'.',
                    'milestones' => [
                        'Core track competencies & skill building',
                        'Guided problem solving and hands-on exercises',
                        'Real-world practical assignments and feedback',
                        'Milestone evaluation and progression certification',
                    ],
                    'trackType' => 'Targeted Progression Track',
                ];

                // Helper to extract description for base milestones (Beginner, Intermediate, Advanced)
                $findBaseLevelDesc = static function (string $baseName) use ($progressionCards, $progressionItems): string {
                    foreach ($progressionCards as $pCard) {
                        if (strcasecmp($pCard['level'], $baseName) === 0 && filled($pCard['details'])) {
                            return trim((string) $pCard['details']);
                        }
                    }
                    foreach ($progressionItems as $pItem) {
                        $lvl = trim((string) ($pItem['level'] ?? ''));
                        if (strcasecmp($lvl, $baseName) === 0 && filled($pItem['details'] ?? null)) {
                            return trim((string) $pItem['details']);
                        }
                    }
                    foreach ($progressionItems as $pItem) {
                        $lvl = trim((string) ($pItem['level'] ?? ''));
                        if (stripos($lvl, ' to ') === false && stripos($lvl, $baseName) !== false && filled($pItem['details'] ?? null)) {
                            return trim((string) $pItem['details']);
                        }
                    }
                    return '';
                };

                // Helper to extract direct explicit description if specifically registered in course
                $findExplicitDesc = static function (string $targetLvl) use ($progressionItems): string {
                    foreach ($progressionItems as $pItem) {
                        $lvl = trim((string) ($pItem['level'] ?? ''));
                        if (strcasecmp($lvl, $targetLvl) === 0 && filled($pItem['details'] ?? null)) {
                            return trim((string) $pItem['details']);
                        }
                    }
                    return '';
                };

                $beginnerDesc = $findBaseLevelDesc('Beginner');
                $intermediateDesc = $findBaseLevelDesc('Intermediate');
                $advancedDesc = $findBaseLevelDesc('Advanced');

                $levelSections = [];

                if ($matchedKey === 'Advanced (Beginner to Advanced)') {
                    // Combine the description for ALL levels: Beginner, Intermediate, and Advanced
                    $explicit = $findExplicitDesc('Advanced (Beginner to Advanced)');
                    if ($explicit !== '') {
                        $levelSections[] = ['level' => 'Comprehensive Track Overview', 'details' => $explicit];
                    }
                    if ($beginnerDesc !== '') {
                        $levelSections[] = ['level' => 'Beginner Milestone', 'details' => $beginnerDesc];
                    }
                    if ($intermediateDesc !== '') {
                        $levelSections[] = ['level' => 'Intermediate Milestone', 'details' => $intermediateDesc];
                    }
                    if ($advancedDesc !== '') {
                        $levelSections[] = ['level' => 'Advanced Milestone', 'details' => $advancedDesc];
                    }
                } elseif ($matchedKey === 'Advanced (Intermediate to Advanced)') {
                    // Only get the description for Intermediate and Advanced
                    $explicit = $findExplicitDesc('Advanced (Intermediate to Advanced)');
                    if ($explicit !== '') {
                        $levelSections[] = ['level' => 'Specialist Track Overview', 'details' => $explicit];
                    }
                    if ($intermediateDesc !== '') {
                        $levelSections[] = ['level' => 'Intermediate Milestone', 'details' => $intermediateDesc];
                    }
                    if ($advancedDesc !== '') {
                        $levelSections[] = ['level' => 'Advanced Milestone', 'details' => $advancedDesc];
                    }
                } elseif ($matchedKey === 'Intermediate (Beginner to Intermediate)') {
                    // Bridge track: combine Beginner and Intermediate
                    $explicit = $findExplicitDesc('Intermediate (Beginner to Intermediate)');
                    if ($explicit !== '') {
                        $levelSections[] = ['level' => 'Bridge Track Overview', 'details' => $explicit];
                    }
                    if ($beginnerDesc !== '') {
                        $levelSections[] = ['level' => 'Beginner Milestone', 'details' => $beginnerDesc];
                    }
                    if ($intermediateDesc !== '') {
                        $levelSections[] = ['level' => 'Intermediate Milestone', 'details' => $intermediateDesc];
                    }
                } elseif ($matchedKey === 'Beginner') {
                    if ($beginnerDesc !== '') {
                        $levelSections[] = ['level' => 'Beginner Milestone', 'details' => $beginnerDesc];
                    }
                } elseif ($matchedKey === 'Intermediate') {
                    if ($intermediateDesc !== '') {
                        $levelSections[] = ['level' => 'Intermediate Milestone', 'details' => $intermediateDesc];
                    }
                } elseif ($matchedKey === 'Advanced') {
                    if ($advancedDesc !== '') {
                        $levelSections[] = ['level' => 'Advanced Milestone', 'details' => $advancedDesc];
                    }
                } else {
                    $explicit = $findExplicitDesc($rawTrim);
                    if ($explicit !== '') {
                        $levelSections[] = ['level' => $rawTrim.' Milestone', 'details' => $explicit];
                    }
                }

                // Fallback to any progression items if levelSections is still empty
                if (empty($levelSections)) {
                    foreach ($progressionItems as $pItem) {
                        $pLevel = trim((string) ($pItem['level'] ?? ''));
                        if (filled($pItem['details'] ?? null)) {
                            $levelSections[] = [
                                'level' => $pLevel !== '' ? $pLevel : 'Course Progression',
                                'details' => trim((string) $pItem['details']),
                            ];
                        }
                    }
                }

                $courseDetails = '';
                if (! empty($levelSections)) {
                    $courseDetails = implode("\n\n", array_map(
                        static fn (array $sec): string => $sec['level'].":\n".$sec['details'],
                        $levelSections
                    ));
                }

                return [
                    'levelName' => $rawTrim !== '' ? $rawTrim : ($matchedKey ?? 'Skill Level'),
                    'tier' => $framework['tier'],
                    'badge' => $framework['badge'],
                    'badgeColor' => $framework['badgeColor'],
                    'target' => $framework['target'],
                    'focus' => $framework['focus'],
                    'milestones' => $framework['milestones'],
                    'trackType' => $framework['trackType'],
                    'courseDetails' => $courseDetails,
                    'courseTitle' => $course->title,
                    'levelSections' => $levelSections,
                ];
            };
        @endphp

        {{-- Dark Banner Section --}}
        <section class="bg-[#0a2d27] relative overflow-hidden py-12 lg:py-16">
            <div class="pointer-events-none absolute -top-24 -right-24 h-80 w-80 rounded-full bg-teal-500/15 blur-3xl"></div>
            <div class="mx-auto max-w-6xl px-6 lg:px-8 relative z-10">
                <nav aria-label="Breadcrumb" class="text-xs font-medium text-slate-300">
                    <ol class="flex flex-wrap items-center gap-2">
                        <li><a href="{{ route('home') }}" class="hover:text-yellow-400 transition">Home</a></li>
                        <li class="text-slate-500">/</li>
                        <li><a href="{{ route('landing.courses') }}" class="hover:text-yellow-400 transition">Courses</a></li>
                        <li class="text-slate-500">/</li>
                        <li class="text-white font-semibold truncate max-w-xs sm:max-w-md">{{ $course->title }}</li>
                    </ol>
                </nav>
                <div class="flex flex-wrap items-center gap-2.5 mt-4">
                    <span class="inline-flex items-center rounded-full border border-yellow-400/30 bg-yellow-400/10 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-yellow-300">{{ $course->code }}</span>
                    @if ($isLockedCourse)
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-400/30 bg-amber-400/10 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-amber-200">
                            <i class="fa-solid fa-lock text-[9px]"></i> Exclusive / Locked Cohort
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-400/30 bg-emerald-400/10 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span> Open Enrollment
                        </span>
                    @endif
                </div>
                <h1 class="mt-3.5 max-w-4xl text-3xl font-black text-white sm:text-4xl lg:text-5xl tracking-tight leading-tight">{{ $course->title }}</h1>
                <p class="mt-4 max-w-3xl text-sm sm:text-base text-slate-300 leading-relaxed">{{ \Illuminate\Support\Str::words((string) ($course->overview ?: $course->description), 80) }}</p>
                @php
                    $avgRating = (float) ($course->average_rating ?? ($course->ratings_avg_rating ?? 0));
                    $ratingCount = (int) ($course->review_count ?? ($course->ratings_count ?? 0));
                @endphp
                <div class="mt-4 flex flex-wrap items-center gap-4 text-xs text-slate-300">
                    <x-rating-stars :rating="$avgRating" :count="$ratingCount" size="sm" />
                    <span class="text-slate-500">•</span>
                    <span>
                        <strong class="text-white">Instructor:</strong>
                        @if ($course->instructors->isNotEmpty())
                            @foreach ($course->instructors as $inst)
                                <a href="{{ route('landing.instructors.show', ['instructor' => $inst->id, 'slug' => \Illuminate\Support\Str::slug($inst->name ?: (string) $inst->id)]) }}" class="text-yellow-300 hover:text-yellow-200 underline underline-offset-2 font-semibold transition">{{ $inst->name }}</a>@if (! $loop->last)<span class="text-slate-400"> / </span>@endif
                            @endforeach
                        @else
                            {{ $course->instructor_label }}
                        @endif
                    </span>
                </div>
                <div class="mt-7 flex flex-wrap items-center gap-3">
                    @if (! $isLockedCourse)
                        @php
                            $feeOptions = $course->getFeeOptions();
                            $hasMultipleOptions = count($feeOptions) > 1;
                            $singleOptionParams = ! empty($feeOptions[0])
                                ? ['course' => $course, 'track' => $feeOptions[0]['level'], 'mode' => $feeOptions[0]['category']]
                                : ['course' => $course];
                            $courseModalData = [
                                'id' => $course->id,
                                'title' => $course->title,
                                'code' => $course->code,
                                'checkoutUrl' => route('checkout.show', $course),
                                'options' => $feeOptions,
                            ];
                        @endphp
                        @if ($hasMultipleOptions)
                            <button
                                type="button"
                                onclick="window.openCourseOptionModal(@js($courseModalData))"
                                @click.prevent="window.openCourseOptionModal(@js($courseModalData))"
                                class="inline-flex items-center gap-2 rounded-full bg-yellow-400 px-7 py-3 text-xs sm:text-sm font-bold text-[#0a2d27] shadow-md transition hover:-translate-y-0.5 hover:bg-amber-300 cursor-pointer"
                            >
                                <i class="fa-solid fa-credit-card"></i>
                                <span>Enroll &amp; Pay (Instant Access)</span>
                            </button>
                        @else
                            <a href="{{ route('checkout.show', $singleOptionParams) }}" class="inline-flex items-center gap-2 rounded-full bg-yellow-400 px-7 py-3 text-xs sm:text-sm font-bold text-[#0a2d27] shadow-md transition hover:-translate-y-0.5 hover:bg-amber-300">
                                <i class="fa-solid fa-credit-card"></i>
                                <span>Enroll &amp; Pay (Instant Access)</span>
                            </a>
                        @endif
                    @else
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/10 border border-white/20 px-6 py-3 text-xs sm:text-sm font-bold text-slate-300 cursor-not-allowed">
                            <i class="fa-solid fa-lock text-amber-400"></i>
                            <span>Enrollment Locked</span>
                        </span>
                    @endif
                    <a href="{{ route('landing.courses') }}" class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/5 px-5 py-3 text-xs sm:text-sm font-semibold text-white transition hover:bg-white/15">
                        <i class="fa-solid fa-arrow-left text-xs"></i>
                        <span>Back to Courses</span>
                    </a>
                </div>
            </div>
        </section>

        {{-- Main Course Details --}}
        <section class="py-10 sm:py-14 lg:py-16">
            <div class="mx-auto grid max-w-6xl items-start gap-8 px-4 sm:px-6 lg:grid-cols-3 lg:gap-10 lg:px-8">

                {{-- Left Content Column --}}
                <div class="min-w-0 lg:col-span-2 space-y-6">
                    @if (filled($course->overview) || filled($course->description))
                        <div class="rounded-2xl border border-slate-200/90 bg-white p-6 space-y-5 shadow-2xs">
                            @if (filled($course->overview))
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-teal-50 text-teal-600 text-xs"><i class="fa-solid fa-compass"></i></span>
                                        <h2 class="text-lg font-bold text-slate-900">Course Overview</h2>
                                    </div>
                                    <p class="text-sm sm:text-base leading-relaxed text-slate-600">{{ \Illuminate\Support\Str::words((string) $course->overview, 80) }}</p>
                                </div>
                            @endif

                            @if (filled($course->description))
                                <div class="{{ filled($course->overview) ? 'border-t border-slate-100 pt-4' : '' }} space-y-2">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-slate-700 text-xs"><i class="fa-solid fa-align-left"></i></span>
                                        <h2 class="text-base font-bold text-slate-900">Detailed Description</h2>
                                    </div>
                                    <p class="text-sm leading-relaxed text-slate-600">{{ \Illuminate\Support\Str::limit(trim((string) $course->description), 150) }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    @php
                        $requirementLines = array_values(array_filter(array_map(
                            static fn (string $line): string => trim((string) preg_replace('/^(?:[-*•]+|\d+[.)])\s*/u', '', trim($line))),
                            $toLines($course->requirements)
                        )));
                    @endphp
                    @if (! empty($requirementLines))
                        <div class="rounded-2xl border border-slate-200/90 bg-white p-6 space-y-3.5 shadow-2xs">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-teal-50 text-teal-600 text-xs"><i class="fa-solid fa-list-check"></i></span>
                                <h3 class="text-base font-bold text-slate-900">Requirements &amp; Prerequisites</h3>
                            </div>
                            <ul class="grid gap-2.5 sm:grid-cols-2">
                                @foreach ($requirementLines as $reqItem)
                                    <li class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/70 p-3 text-xs sm:text-sm leading-relaxed text-slate-700">
                                        <span class="mt-0.5 inline-flex h-4.5 w-4.5 shrink-0 items-center justify-center rounded-full bg-teal-100 text-teal-700">
                                            <i class="fa-solid fa-check text-[9px]"></i>
                                        </span>
                                        <span>{{ $reqItem }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($course->isOngoing())
                        <div class="space-y-4 rounded-2xl border border-teal-200/80 bg-gradient-to-br from-teal-50/70 to-white p-6 shadow-2xs">
                            <div class="flex items-center justify-between gap-3 flex-wrap">
                                <div>
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-teal-700">Course Schedule &amp; Intakes</span>
                                    <h2 class="text-lg font-black text-slate-900 mt-0.5">Cohort &amp; Class Intakes</h2>
                                </div>
                                <span class="rounded-full bg-teal-100/80 px-3 py-1 text-[11px] font-bold text-teal-900 border border-teal-200">
                                    <i class="fa-solid fa-arrows-rotate mr-1 text-teal-600"></i> Ongoing Course
                                </span>
                            </div>

                            @php
                                $activeIntake = $course->activeIntake;
                                $upcomingIntakes = $course->upcomingIntakes;
                            @endphp

                            @if ($activeIntake)
                                <div class="rounded-xl border border-teal-200 bg-white p-4">
                                    <div class="flex items-center justify-between gap-2 flex-wrap mb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            <h3 class="text-sm sm:text-base font-bold text-slate-900">{{ $activeIntake->name }}</h3>
                                        </div>
                                        <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700 border border-emerald-200">
                                            Current Active Intake
                                        </span>
                                    </div>
                                    <div class="grid gap-3 sm:grid-cols-2 text-xs text-slate-600 mt-3 pt-3 border-t border-slate-100">
                                        <div>
                                            <span class="font-semibold text-slate-500 block">Intake Duration</span>
                                            <span class="font-bold text-slate-900">{{ $activeIntake->formattedDateRange() ?? 'Ongoing / In Session' }}</span>
                                        </div>
                                        @if ($activeIntake->next_intake_start_date)
                                            <div>
                                                <span class="font-semibold text-slate-500 block">Next Intake Starts</span>
                                                <span class="font-bold text-teal-700">{{ $activeIntake->formattedNextIntake() }}</span>
                                            </div>
                                        @endif
                                        @if ($activeIntake->registration_deadline)
                                            <div>
                                                <span class="font-semibold text-slate-500 block">Registration Deadline</span>
                                                <span class="font-bold text-slate-800">{{ $activeIntake->registration_deadline->format('M j, Y') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @elseif ($upcomingIntakes->isNotEmpty())
                                @php $next = $upcomingIntakes->first(); @endphp
                                <div class="rounded-xl border border-amber-200 bg-amber-50/50 p-4">
                                    <h3 class="text-sm font-bold text-amber-900">Next Upcoming Intake: {{ $next->name }}</h3>
                                    <p class="text-xs text-amber-700 mt-1">Starting on: <strong>{{ $next->start_date ? $next->start_date->format('M j, Y') : 'TBA' }}</strong></p>
                                </div>
                            @else
                                <p class="text-xs text-slate-500 italic">New intake schedule will be announced soon.</p>
                            @endif
                        </div>
                    @endif

                    @if (filled($course->key_outcome))
                        <div class="rounded-2xl border border-emerald-200/80 bg-gradient-to-br from-emerald-50/50 to-white p-6 space-y-2.5 shadow-2xs">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 text-xs"><i class="fa-solid fa-trophy"></i></span>
                                <h3 class="text-base font-bold text-slate-900">Key Outcome</h3>
                            </div>
                            <p class="text-sm leading-relaxed text-slate-700">{{ \Illuminate\Support\Str::words((string) $course->key_outcome, 100) }}</p>
                        </div>
                    @endif

                    @if (! empty($feeSections))
                        <div class="rounded-2xl border border-slate-200/90 bg-white p-6 space-y-5 shadow-2xs">
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-teal-50 text-teal-600 text-xs"><i class="fa-solid fa-tags"></i></span>
                                    <h3 class="text-base font-bold text-slate-900">Tuition &amp; Fee Structure</h3>
                                </div>
                                <span class="text-[11px] font-medium text-slate-500">Click any level to inspect its progression roadmap</span>
                            </div>

                            <div class="space-y-5">
                                @foreach ($feeSections as $section)
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">{{ $section['label'] }}</h4>
                                            @if(!empty($section['badge']))
                                                <span class="rounded-full bg-teal-50 px-2.5 py-0.5 text-[10px] font-semibold text-teal-700 border border-teal-200/60">{{ $section['badge'] }}</span>
                                            @endif
                                        </div>
                                        <div class="overflow-hidden rounded-xl border border-slate-200/90 divide-y divide-slate-100">
                                            <div class="grid grid-cols-[1fr_95px_75px] sm:grid-cols-[1fr_130px_100px] items-center gap-2 sm:gap-4 bg-slate-50/90 px-4 py-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                                <span>Track / Level</span>
                                                <span class="text-right">Fee</span>
                                                <span class="text-right">Duration</span>
                                            </div>
                                            @foreach ($section['rows'] as $row)
                                                @php
                                                    $levelName = $row['level'] !== '' ? $row['level'] : 'Level';
                                                    $levelInfo = $getLevelGuidelines($levelName);
                                                @endphp
                                                <div class="hub-fee-row grid grid-cols-[1fr_95px_75px] sm:grid-cols-[1fr_130px_100px] w-full items-center gap-2 sm:gap-4 bg-white px-4 py-3 transition hover:bg-teal-50/30">
                                                    <div class="min-w-0 text-left">
                                                        <button
                                                            type="button"
                                                            onclick="window.openSkillLevelModal(@js($levelInfo))"
                                                            @click.prevent="window.openSkillLevelModal(@js($levelInfo))"
                                                            class="group/level inline-flex flex-wrap items-center gap-2 text-left focus:outline-none cursor-pointer max-w-full"
                                                            title="Click to view {{ $levelName }} Progression Roadmap"
                                                        >
                                                            <span class="text-xs sm:text-sm font-bold text-slate-800 group-hover/level:text-teal-700 transition leading-snug">
                                                                {{ $row['level'] !== '' ? $row['level'] : '-' }}
                                                            </span>
                                                            <span class="inline-flex items-center gap-1 rounded-full bg-teal-50 border border-teal-200/80 px-2 py-0.5 text-[10px] font-semibold text-teal-700 group-hover/level:bg-teal-600 group-hover/level:text-white group-hover/level:border-teal-600 transition-colors whitespace-nowrap">
                                                                <i class="fa-regular fa-eye text-[9px]"></i>
                                                                <span>Roadmap</span>
                                                            </span>
                                                        </button>
                                                    </div>

                                                    <div class="text-right">
                                                        <span class="text-xs sm:text-sm font-extrabold text-slate-900 tabular-nums block">
                                                            {{ $row['amount'] !== '' ? $row['amount'] : '-' }}
                                                        </span>
                                                    </div>

                                                    <div class="text-right">
                                                        <span class="text-xs text-slate-500 font-medium tabular-nums block leading-tight">
                                                            {{ $row['duration'] !== '' ? $row['duration'] : '-' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if (! empty($progressionCards))
                        <div class="rounded-2xl border border-slate-200/90 bg-white p-6 space-y-4 shadow-2xs">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 text-xs"><i class="fa-solid fa-layer-group"></i></span>
                                <h3 class="text-base font-bold text-slate-900">Levels &amp; Progression</h3>
                            </div>
                            <div class="divide-y divide-slate-100 overflow-hidden rounded-xl border border-slate-200/80 bg-slate-50/40">
                                @foreach ($progressionCards as $item)
                                    <div class="p-4 space-y-1">
                                        <h4 class="text-sm font-bold text-slate-900">{{ $item['level'] }}</h4>
                                        <p class="text-xs sm:text-sm leading-relaxed text-slate-600">{{ $item['details'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Sidebar Quick Facts Card --}}
                <aside class="sticky top-24 min-w-0 rounded-2xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-xs lg:col-span-1">
                    <div class="relative h-44 w-full overflow-hidden rounded-xl bg-slate-100 mb-5">
                        <img src="{{ $course->course_image_url }}" class="w-full h-full object-cover" alt="{{ $course->title }}">
                        @if ($course->code)
                            <span class="absolute top-2.5 right-2.5 rounded-full bg-slate-950/70 backdrop-blur-xs border border-white/15 px-2.5 py-0.5 text-[10px] font-bold text-white">
                                {{ $course->code }}
                            </span>
                        @endif
                    </div>
                    <h2 class="text-base font-bold text-slate-900">Quick Facts</h2>
                    <dl class="mt-3.5 divide-y divide-slate-100 text-xs sm:text-sm">
                        <div class="py-2.5 flex items-center justify-between gap-2">
                            <dt class="font-medium text-slate-500">Course Code</dt>
                            <dd class="font-bold text-slate-900">{{ $course->code }}</dd>
                        </div>
                        <div class="py-2.5 flex items-center justify-between gap-2">
                            <dt class="font-medium text-slate-500">Instructor</dt>
                            <dd class="font-bold text-slate-900 text-right truncate max-w-[180px]" title="{{ $course->instructor_label }}">
                                @if ($course->instructors->isNotEmpty())
                                    @foreach ($course->instructors as $inst)
                                        <a href="{{ route('landing.instructors.show', ['instructor' => $inst->id, 'slug' => \Illuminate\Support\Str::slug($inst->name ?: (string) $inst->id)]) }}" class="text-teal-700 hover:text-[#0a2d27] hover:underline underline-offset-2 transition">{{ $inst->name }}</a>@if (! $loop->last)<span class="text-slate-400"> / </span>@endif
                                    @endforeach
                                @else
                                    {{ $course->instructor_label }}
                                @endif
                            </dd>
                        </div>
                        <div class="py-2.5 flex items-center justify-between gap-2">
                            <dt class="font-medium text-slate-500">Structure</dt>
                            <dd class="font-bold">
                                @if ($course->isOngoing())
                                    <span class="text-teal-700">Ongoing (Intakes)</span>
                                @else
                                    <span class="text-slate-800">Once-off / Flexible</span>
                                @endif
                            </dd>
                        </div>
                        @if ($course->isOngoing() && $course->activeIntake)
                            <div class="py-2.5">
                                <div class="flex items-center justify-between gap-2">
                                    <dt class="font-medium text-slate-500">Active Intake</dt>
                                    <dd class="font-bold text-slate-900">{{ $course->activeIntake->name }}</dd>
                                </div>
                                <p class="text-[11px] text-slate-500 text-right mt-0.5">{{ $course->activeIntake->formattedDateRange() }}</p>
                            </div>
                        @endif
                        <div class="py-2.5 flex items-center justify-between gap-2">
                            <dt class="font-medium text-slate-500">Timeline</dt>
                            <dd class="font-bold text-slate-900 text-right">{{ $course->timeline ?: 'Self paced' }}</dd>
                        </div>
                        <div class="py-2.5 flex items-center justify-between gap-2">
                            <dt class="font-medium text-slate-500">Rating</dt>
                            <dd><x-rating-stars :rating="$avgRating" :count="$ratingCount" size="xs" /></dd>
                        </div>
                    </dl>

                    @if ($isLockedCourse)
                        <button type="button" disabled class="mt-5 inline-flex w-full items-center justify-center rounded-full bg-slate-100 px-5 py-3 text-xs sm:text-sm font-bold text-slate-400 cursor-not-allowed border border-slate-200">
                            <i class="fa-solid fa-lock mr-2 text-amber-500"></i> Enrollment Locked
                        </button>
                    @else
                        @if ($hasMultipleOptions ?? false)
                            <button
                                type="button"
                                onclick="window.openCourseOptionModal(@js($courseModalData))"
                                @click.prevent="window.openCourseOptionModal(@js($courseModalData))"
                                class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-full bg-yellow-400 px-5 py-3 text-xs sm:text-sm font-bold text-[#0a2d27] shadow-xs transition hover:bg-amber-300 cursor-pointer"
                            >
                                <i class="fa-solid fa-credit-card"></i>
                                <span>Enroll &amp; Pay</span>
                            </button>
                        @else
                            <a href="{{ route('checkout.show', $singleOptionParams ?? ['course' => $course]) }}" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-full bg-yellow-400 px-5 py-3 text-xs sm:text-sm font-bold text-[#0a2d27] shadow-xs transition hover:bg-amber-300">
                                <i class="fa-solid fa-credit-card"></i>
                                <span>Enroll &amp; Pay</span>
                            </a>
                        @endif
                    @endif
                </aside>

            </div>
        </section>

        {{-- Ratings & Reviews Section --}}
        <section class="py-12 lg:py-16 border-t border-slate-200/60">
            <div class="mx-auto max-w-6xl px-6 lg:px-8">
                <div class="mb-8">
                    <span class="text-teal-600 font-bold uppercase tracking-[0.2em] text-xs">Verified Student Feedback</span>
                    <h2 class="text-2xl font-black text-slate-900 mt-2 sm:text-3xl tracking-tight">Course Ratings &amp; Reviews</h2>
                    <p class="mt-1.5 text-slate-600 text-sm">Honest ratings and reviews from students enrolled in {{ $course->title }}.</p>
                </div>

                <livewire:reviews.review-list target-type="course" :target-id="$course->id" :target-title="$course->title" />
            </div>
        </section>

        <livewire:reviews.submit-review-modal />

        @if ($relatedCourses->isNotEmpty())
            <section class="pb-16 lg:pb-20">
                <div class="mx-auto max-w-6xl px-6 lg:px-8">
                    <h2 class="text-2xl font-black text-slate-900 sm:text-3xl tracking-tight">Related Courses</h2>
                    <p class="mt-1.5 text-sm text-slate-600">Explore other practical tracks that can complement this learning path.</p>
                    <div class="mt-6 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($relatedCourses as $relatedCourse)
                            <article class="group rounded-2xl border border-slate-200/90 bg-white p-3.5 hover:-translate-y-1 transition-all flex flex-col justify-between">
                                <div>
                                    <div class="relative h-36 w-full overflow-hidden rounded-xl bg-slate-100 mb-3">
                                        <a href="{{ route('landing.courses.show', ['course' => $relatedCourse->id, 'slug' => $relatedCourse->seo_slug]) }}" class="block w-full h-full">
                                            <img src="{{ $relatedCourse->course_image_url }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $relatedCourse->title }}">
                                        </a>
                                        <div class="absolute top-2.5 left-2.5 bg-slate-950/70 backdrop-blur-xs text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full border border-white/15">{{ $relatedCourse->code }}</div>
                                    </div>
                                    <h3 class="text-sm sm:text-base font-bold text-slate-900 leading-snug line-clamp-2">
                                        <a href="{{ route('landing.courses.show', ['course' => $relatedCourse->id, 'slug' => $relatedCourse->seo_slug]) }}" class="hover:text-teal-700 transition-colors">
                                            {{ $relatedCourse->title }}
                                        </a>
                                    </h3>
                                    <p class="mt-1.5 text-xs leading-relaxed text-slate-600 line-clamp-2">{{ \Illuminate\Support\Str::limit($relatedCourse->overview, 120) }}</p>
                                </div>
                                <div class="mt-4 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                                    <a href="{{ route('landing.courses.show', ['course' => $relatedCourse->id, 'slug' => $relatedCourse->seo_slug]) }}" class="inline-flex items-center rounded-full bg-[#0a2d27] px-3.5 py-1.5 text-[11px] font-bold text-white transition hover:bg-[#11443c]">View Course &rarr;</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </main>

    @include('partials.public-footer')

    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Course',
            'name' => $course->title,
            'description' => $course->overview ?: $course->description,
            'provider' => [
                '@type' => 'EducationalOrganization',
                'name' => 'think.er HUB',
                'sameAs' => url('/'),
            ],
            'url' => route('landing.courses.show', ['course' => $course->id, 'slug' => $slug]),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Home',
                    'item' => route('home'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Courses',
                    'item' => route('landing.courses'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $course->title,
                    'item' => route('landing.courses.show', ['course' => $course->id, 'slug' => $slug]),
                ],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @include('partials.skill-level-progression-modal')
    @include('partials.course-selection-modal')
    @include('partials.legal-modals')
</body>
</html>