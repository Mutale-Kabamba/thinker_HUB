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
        @media (max-width: 640px) {
            .hub-fee-row {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 0.25rem !important;
            }
        }
    </style>
</head>
<body class="hub-public bg-[#f8fcf9] text-slate-900 font-sans antialiased" x-data="{ mobileMenu: false }">

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
                        default => ucwords(str_replace('_', ' ', (string) $key)),
                    };

                    $badge = match ($key) {
                        'one_on_one' => '1:1 Focus',
                        'group' => 'Best Value',
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
                        'fees' => array_values(array_filter($fallbackRows, static fn (array $row): bool => ! in_array(($row['mode'] ?? 'fees'), ['one_on_one', 'group'], true))),
                    ];

                    foreach (['one_on_one', 'group', 'fees'] as $sectionKey) {
                        if ($groupedRows[$sectionKey] === []) {
                            continue;
                        }

                        $feeSections[] = [
                            'key' => $sectionKey,
                            'label' => match ($sectionKey) {
                                'one_on_one' => 'One-on-One',
                                'group' => 'Group',
                                default => 'Fees',
                            },
                            'badge' => match ($sectionKey) {
                                'one_on_one' => '1:1 Focus',
                                'group' => 'Best Value',
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
        @endphp

        {{-- Dark Banner Section --}}
        <section class="bg-[#0a2d27] py-16 lg:py-20">
            <div class="mx-auto max-w-6xl px-6 lg:px-8">
                <nav aria-label="Breadcrumb" class="text-sm text-slate-300">
                    <ol class="flex flex-wrap items-center gap-2">
                        <li><a href="{{ route('home') }}" class="hover:text-yellow-400">Home</a></li>
                        <li>/</li>
                        <li><a href="{{ route('landing.courses') }}" class="hover:text-yellow-400">Courses</a></li>
                        <li>/</li>
                        <li class="text-white">{{ $course->title }}</li>
                    </ol>
                </nav>
                <div class="flex items-center gap-3 mt-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-yellow-400">{{ $course->code }}</p>
                    @if ($isLockedCourse)
                        <span class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-white">Locked To Selected Participants</span>
                    @endif
                </div>
                <h1 class="mt-4 max-w-4xl text-4xl font-black text-white sm:text-5xl">{{ $course->title }}</h1>
                <p class="mt-5 max-w-3xl text-slate-300">{{ $course->overview ?: $course->description }}</p>
                @php
                    $avgRating = round((float) ($course->ratings_avg_rating ?? 0), 1);
                    $ratingCount = (int) ($course->ratings_count ?? 0);
                @endphp
                <div class="mt-4 flex items-center gap-2">
                    <div class="flex items-center gap-1 text-sm">
                        @for ($star = 1; $star <= 5; $star++)
                            @if ($star <= floor($avgRating))
                                <i class="fa-solid fa-star text-yellow-400"></i>
                            @elseif ($star - $avgRating < 1 && $star - $avgRating > 0)
                                <i class="fa-solid fa-star-half-stroke text-yellow-400"></i>
                            @else
                                <i class="fa-regular fa-star text-slate-500"></i>
                            @endif
                        @endfor
                    </div>
                    <span class="text-sm font-semibold text-white">{{ $avgRating > 0 ? $avgRating : '' }}</span>
                    <span class="text-sm text-slate-400">({{ $ratingCount }} {{ Str::plural('review', $ratingCount) }})</span>
                </div>
                <div class="mt-8 flex flex-wrap items-center gap-4">
                    @if (! $isLockedCourse)
                        <a href="{{ route('checkout.show', $course) }}" class="inline-flex items-center gap-2 rounded-full bg-yellow-400 px-8 py-3.5 text-sm font-bold text-[#0a2d27] shadow-lg shadow-yellow-400/20 transition hover:bg-yellow-300">
                            <i class="fa-solid fa-credit-card"></i>
                            Enroll &amp; Pay (Instant Access)
                        </a>
                    @else
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/10 border border-white/20 px-6 py-3.5 text-sm font-bold text-slate-300 cursor-not-allowed">
                            <i class="fa-solid fa-lock text-amber-400"></i>
                            Enrollment Locked
                        </span>
                    @endif
                    <a href="{{ route('landing.courses') }}" class="inline-flex items-center gap-2 rounded-full border border-white/20 px-5 py-3 text-sm font-semibold text-white hover:bg-white/10">
                        <i class="fa-solid fa-arrow-left"></i>
                        Back to Courses
                    </a>
                </div>
            </div>
        </section>

        {{-- Main Course Details --}}
        <section class="py-12 lg:py-16">
            <div class="mx-auto grid max-w-6xl items-start gap-10 px-6 lg:grid-cols-3 lg:gap-12 lg:px-8">
                
                {{-- Left Content Column (Strictly Unboxed) --}}
                <div class="min-w-0 lg:col-span-2 space-y-10">
                    @if (filled($course->overview))
                        <div class="space-y-3">
                            <h2 class="text-xl font-bold text-slate-900">Course Overview</h2>
                            <p class="leading-relaxed text-slate-600">{{ $course->overview }}</p>
                        </div>
                    @endif

                    @if (filled($course->key_outcome))
                        <div class="space-y-3">
                            <h3 class="text-lg font-bold text-slate-900">Key Outcome</h3>
                            <p class="leading-relaxed text-slate-600">{{ $course->key_outcome }}</p>
                        </div>
                    @endif

                    @if (! empty($feeSections))
                        <div class="space-y-4">
                            <h3 class="text-lg font-bold text-slate-900">Fees</h3>
                            <div class="space-y-6">
                                @foreach ($feeSections as $section)
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">{{ $section['label'] }}</h4>
                                            @if(!empty($section['badge']))
                                                <span class="rounded-full bg-teal-50 px-2.5 py-0.5 text-[10px] font-semibold text-teal-700 border border-teal-200/60">{{ $section['badge'] }}</span>
                                            @endif
                                        </div>
                                        <div class="space-y-2">
                                            @foreach ($section['rows'] as $row)
                                                <div class="hub-fee-row flex w-full items-center justify-between gap-4 rounded-xl border border-slate-200/80 bg-white px-4 py-3 shadow-sm">
                                                    <span class="text-sm font-semibold text-slate-800">{{ $row['level'] !== '' ? $row['level'] : '-' }}</span>
                                                    <span class="text-sm font-bold text-slate-900">{{ $row['amount'] !== '' ? $row['amount'] : '-' }}</span>
                                                    <span class="text-sm text-slate-500">{{ $row['duration'] !== '' ? $row['duration'] : '-' }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if (! empty($progressionCards))
                        <div class="space-y-4">
                            <h3 class="text-lg font-bold text-slate-900">Levels &amp; Progression</h3>
                            <div class="divide-y divide-slate-100 overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm">
                                @foreach ($progressionCards as $item)
                                    <div class="p-5 space-y-1">
                                        <h4 class="text-base font-bold text-slate-800">{{ $item['level'] }}</h4>
                                        <p class="text-sm leading-relaxed text-slate-600">{{ $item['details'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Sidebar Quick Facts Card --}}
                <aside class="sticky top-6 min-w-0 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm lg:col-span-1">
                    <h2 class="text-lg font-bold text-slate-900">Quick Facts</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div>
                            <dt class="font-semibold text-slate-500">Code</dt>
                            <dd class="mt-0.5 font-bold text-slate-900">{{ $course->code }}</dd>
                        </div>
                        <div class="border-t border-slate-100 pt-3">
                            <dt class="font-semibold text-slate-500">Timeline</dt>
                            <dd class="mt-0.5 font-bold text-slate-900">{{ $course->timeline ?: 'Self paced' }}</dd>
                        </div>
                    </dl>
                    
                    @if ($isLockedCourse)
                        <button type="button" disabled class="mt-6 inline-flex w-full items-center justify-center rounded-full bg-slate-100 px-5 py-3 text-sm font-bold text-slate-400 cursor-not-allowed border border-slate-200">
                            <i class="fa-solid fa-lock mr-2 text-amber-500"></i> Enrollment Locked
                        </button>
                    @else
                        <a href="{{ route('checkout.show', $course) }}" class="mt-6 inline-flex w-full items-center justify-center rounded-full bg-yellow-400 px-5 py-3 text-sm font-bold text-[#0a2d27] transition hover:bg-yellow-300 shadow-sm">
                            <i class="fa-solid fa-credit-card mr-2"></i> Enroll &amp; Pay
                        </a>
                    @endif

                    <div class="mt-6 border-t border-slate-100 pt-4">
                        <h3 class="text-sm font-bold text-slate-900">Rating</h3>
                        <div class="mt-2 flex items-center gap-1.5 text-sm">
                            <div class="flex items-center gap-1 text-sm">
                                @for ($star = 1; $star <= 5; $star++)
                                    @if ($star <= floor($avgRating))
                                        <i class="fa-solid fa-star text-yellow-500"></i>
                                    @elseif ($star - $avgRating < 1 && $star - $avgRating > 0)
                                        <i class="fa-solid fa-star-half-stroke text-yellow-500"></i>
                                    @else
                                        <i class="fa-regular fa-star text-slate-300"></i>
                                    @endif
                                @endfor
                            </div>
                            <span class="font-semibold text-slate-700">{{ $avgRating > 0 ? $avgRating.'/5' : 'N/A' }}</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">{{ $ratingCount }} {{ Str::plural('review', $ratingCount) }}</p>
                    </div>
                </aside>

            </div>
        </section>

        {{-- Ratings & Reviews Section --}}
        @php
            $allRatings = $course->ratings ?? collect();
            $totalRatingsCount = $allRatings->count();
            $avgRating = $totalRatingsCount > 0 ? round((float) $allRatings->avg('rating'), 1) : 0;

            // Star Distribution Calculation
            $starCounts = [
                5 => $allRatings->where('rating', 5)->count(),
                4 => $allRatings->where('rating', 4)->count(),
                3 => $allRatings->where('rating', 3)->count(),
                2 => $allRatings->where('rating', 2)->count(),
                1 => $allRatings->where('rating', 1)->count(),
            ];

            // Written Reviews for 4-second sliding carousel
            $writtenReviews = $allRatings->filter(fn ($r) => filled($r->review))->values();
            $writtenReviewsCount = $writtenReviews->count();
        @endphp

        <section class="py-12 lg:py-16 border-t border-slate-100">
            <div class="mx-auto max-w-6xl px-6 lg:px-8">
                @if (session('success'))
                    <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <p class="font-semibold">We could not save your review.</p>
                        <ul class="mt-1 list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Side-by-Side Minimal Layout: Reviews (Left) | Ratings (Right) in 2:1 Ratio --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-8 lg:gap-10 items-start">
                    
                    {{-- LEFT COLUMN: REVIEWS SLIDE (2 Parts of 2:1 Ratio -> 8 Columns) --}}
                    <div class="md:col-span-8 md:pr-8 lg:pr-10 pb-8 md:pb-0 border-b md:border-b-0 md:border-r border-slate-200 flex flex-col justify-between min-h-[260px]">
                        <div class="mb-6">
                            <h3 class="text-xl font-bold text-slate-900">Student Reviews</h3>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $writtenReviewsCount }} {{ Str::plural('review', $writtenReviewsCount) }} from learners</p>
                        </div>

                        @if ($writtenReviewsCount > 0)
                            <div x-data="{
                                    active: 0,
                                    total: {{ $writtenReviewsCount }},
                                    timer: null,
                                    start() {
                                        this.stop();
                                        if (this.total > 1) {
                                            this.timer = setInterval(() => {
                                                this.next();
                                            }, 4000);
                                        }
                                    },
                                    stop() {
                                        if (this.timer) clearInterval(this.timer);
                                    },
                                    next() {
                                        this.active = (this.active + 1) % this.total;
                                    },
                                    prev() {
                                        this.active = (this.active - 1 + this.total) % this.total;
                                    },
                                    goTo(idx) {
                                        this.active = idx;
                                    }
                                 }"
                                 x-init="start()"
                                 @mouseenter="stop()"
                                 @mouseleave="start()"
                                 class="relative flex flex-col justify-between flex-1"
                            >
                                {{-- Slide Content --}}
                                <div class="relative min-h-[140px] flex items-center">
                                    @foreach ($writtenReviews as $idx => $r)
                                        <div x-show="active === {{ $idx }}"
                                             x-transition:enter="transition ease-out duration-300 transform opacity-0 translate-x-2"
                                             x-transition:enter-start="opacity-0 translate-x-2"
                                             x-transition:enter-end="opacity-100 translate-x-0"
                                             x-transition:leave="transition ease-in duration-200 transform opacity-100 translate-x-0"
                                             x-transition:leave-start="opacity-100 translate-x-0"
                                             x-transition:leave-end="opacity-0 -translate-x-2"
                                             class="w-full"
                                             style="{{ $idx === 0 ? '' : 'display: none;' }}"
                                        >
                                            {{-- Profile Card | Name (Rating beside name) --}}
                                            <div class="flex items-center gap-3.5">
                                                @if ($r->user?->profile_photo_path)
                                                    <img src="{{ $r->user->getFilamentAvatarUrl() }}" alt="" class="h-11 w-11 rounded-full object-cover shrink-0 border border-slate-200" onerror="this.style.display='none'">
                                                @else
                                                    <div class="h-11 w-11 rounded-full bg-teal-50 text-teal-800 font-bold flex items-center justify-center text-sm shrink-0 border border-teal-100">
                                                        {{ strtoupper(substr($r->user?->name ?? 'S', 0, 1)) }}
                                                    </div>
                                                @endif

                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center flex-wrap gap-2">
                                                        <h4 class="font-bold text-slate-900 text-sm truncate">{{ $r->user?->name ?? 'Student' }}</h4>

                                                        @if ($r->rating)
                                                            <div class="inline-flex items-center gap-0.5 text-yellow-400 text-xs">
                                                                @for ($star = 1; $star <= 5; $star++)
                                                                    @if ($star <= $r->rating)
                                                                        <i class="fa-solid fa-star text-[11px]"></i>
                                                                    @else
                                                                        <i class="fa-regular fa-star text-slate-300 text-[11px]"></i>
                                                                    @endif
                                                                @endfor
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <p class="text-[11px] text-slate-400 mt-0.5">{{ $r->created_at ? $r->created_at->diffForHumans() : 'Recently' }}</p>
                                                </div>
                                            </div>

                                            {{-- Below the name: What they wrote --}}
                                            <p class="mt-3.5 text-sm leading-relaxed text-slate-700">
                                                {{ $r->review }}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Slider Controls (Minimal Dots & Subtle Prev/Next) --}}
                                @if ($writtenReviewsCount > 1)
                                    <div class="flex items-center justify-between pt-4 mt-6 border-t border-slate-100">
                                        <div class="flex items-center gap-1.5">
                                            @foreach ($writtenReviews as $idx => $r)
                                                <button type="button"
                                                        @click="goTo({{ $idx }})"
                                                        class="h-1.5 rounded-full transition-all duration-300"
                                                        :class="active === {{ $idx }} ? 'w-4 bg-teal-600' : 'w-1.5 bg-slate-300 hover:bg-slate-400'"
                                                        aria-label="Review {{ $idx + 1 }}">
                                                </button>
                                            @endforeach
                                        </div>

                                        <div class="flex items-center gap-1.5">
                                            <button type="button"
                                                    @click="prev(); start();"
                                                    class="h-7 w-7 rounded-full text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center transition"
                                                    aria-label="Previous review">
                                                <i class="fa-solid fa-chevron-left text-xs"></i>
                                            </button>
                                            <button type="button"
                                                    @click="next(); start();"
                                                    class="h-7 w-7 rounded-full text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center transition"
                                                    aria-label="Next review">
                                                <i class="fa-solid fa-chevron-right text-xs"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="py-8 text-slate-500 text-sm">
                                <p class="text-slate-600 font-medium">No written reviews yet.</p>
                                <p class="text-xs text-slate-400 mt-1">Be the first enrolled learner to share your experience with this track.</p>
                            </div>
                        @endif
                    </div>

                    {{-- RIGHT COLUMN: RATINGS (1 Part of 2:1 Ratio -> 4 Columns) --}}
                    <div class="md:col-span-4 md:pl-4 lg:pl-6">
                        <div class="text-center md:text-left">
                            <h3 class="text-xl font-bold text-slate-900">User ratings</h3>

                            {{-- Star pill & rating score --}}
                            <div class="mt-3 inline-flex items-center gap-2 rounded-full bg-slate-50 border border-slate-200/80 px-3.5 py-1.5">
                                <div class="flex items-center gap-1 text-yellow-400 text-xs">
                                    @for ($star = 1; $star <= 5; $star++)
                                        @if ($star <= floor($avgRating))
                                            <i class="fa-solid fa-star"></i>
                                        @elseif ($star - $avgRating < 1 && $star - $avgRating > 0)
                                            <i class="fa-solid fa-star-half-stroke"></i>
                                        @else
                                            <i class="fa-regular fa-star text-slate-300"></i>
                                        @endif
                                    @endfor
                                </div>
                                <span class="text-xs font-semibold text-slate-700">{{ $avgRating > 0 ? $avgRating : '0' }} out of 5</span>
                            </div>

                            <p class="mt-2 text-xs text-slate-500">{{ $totalRatingsCount }} user {{ Str::plural('rating', $totalRatingsCount) }}</p>
                        </div>

                        {{-- Star Distribution Breakdown Bars --}}
                        <div class="mt-6 space-y-2.5 max-w-sm">
                            @for ($s = 5; $s >= 1; $s--)
                                @php
                                    $countForStar = $starCounts[$s] ?? 0;
                                    $pct = $totalRatingsCount > 0 ? round(($countForStar / $totalRatingsCount) * 100) : 0;
                                @endphp
                                <div class="flex items-center gap-3 text-xs">
                                    <span class="w-10 font-medium text-slate-600">
                                        {{ $s }} star
                                    </span>
                                    <div class="flex-1 h-2 rounded-full bg-slate-100 overflow-hidden">
                                        <div class="h-full bg-amber-400 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="w-9 text-right font-medium text-slate-500">{{ $pct }}%</span>
                                </div>
                            @endfor
                        </div>
                    </div>

                </div>

                {{-- Rating Form (logged-in enrolled students only) --}}
                @auth
                    @php
                        $isEnrolled = auth()->user()->courses()->where('courses.id', $course->id)->exists();
                        $existingRating = $isEnrolled ? \App\Models\CourseRating::where('course_id', $course->id)->where('user_id', auth()->id())->first() : null;
                    @endphp
                    @if ($isEnrolled)
                        <div class="mt-10 pt-8 border-t border-slate-100 max-w-2xl" x-data='{ rating: @js((int) ($existingRating?->rating ?? 0)), hover: 0, review: @js((string) ($existingRating?->review ?? "")), submitted: false }'>
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-bold text-slate-900">{{ $existingRating ? 'Update Your Rating & Review' : 'Rate & Review This Course' }}</h4>
                                <span class="text-[11px] font-semibold text-teal-700 bg-teal-50 px-2.5 py-0.5 rounded-full border border-teal-200">Enrolled Student</span>
                            </div>
                            <form method="POST" action="{{ route('course.rate', $course->id) }}" @submit="submitted = true">
                                @csrf
                                <input type="hidden" name="rating" :value="rating">
                                <div class="flex items-center gap-1 mb-3">
                                    @for ($star = 1; $star <= 5; $star++)
                                        <button type="button"
                                            @click="rating = {{ $star }}"
                                            @mouseenter="hover = {{ $star }}"
                                            @mouseleave="hover = 0"
                                            class="text-xl transition-transform hover:scale-110 focus:outline-none"
                                        >
                                            <i :class="(hover || rating) >= {{ $star }} ? 'fa-solid fa-star text-yellow-400' : 'fa-regular fa-star text-slate-300'"></i>
                                        </button>
                                    @endfor
                                    <span class="ml-2 text-xs text-slate-500" x-text="rating > 0 ? rating + '/5 Stars' : 'Click to rate'"></span>
                                </div>
                                <textarea
                                    name="review"
                                    x-model="review"
                                    rows="3"
                                    placeholder="Write your review and share what you learned from this course..."
                                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 placeholder-slate-400 focus:border-teal-500 focus:ring-1 focus:ring-teal-500"
                                ></textarea>
                                <div class="mt-3 flex items-center gap-3">
                                    <button
                                        type="submit"
                                        :disabled="rating === 0 || submitted"
                                        class="inline-flex items-center rounded-full bg-[#0a2d27] px-5 py-2 text-xs font-bold text-white transition hover:bg-[#11443c] disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                                    >
                                        {{ $existingRating ? 'Update Review' : 'Submit Review' }}
                                    </button>
                                    @if ($existingRating)
                                        <span class="text-xs text-slate-500">You rated this {{ $existingRating->rating }}/5 on {{ $existingRating->updated_at->format('M j, Y') }}</span>
                                    @endif
                                </div>
                            </form>
                        </div>
                    @endif
                @endauth
            </div>
        </section>

        @if ($relatedCourses->isNotEmpty())
            <section class="pb-20 lg:pb-24">
                <div class="mx-auto max-w-6xl px-6 lg:px-8">
                    <h2 class="text-2xl font-black text-slate-900 sm:text-3xl">Related Courses</h2>
                    <p class="mt-2 text-slate-600">Explore other practical tracks that can complement this learning path.</p>
                    <div class="mt-8 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($relatedCourses as $relatedCourse)
                            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-teal-600">{{ $relatedCourse->code }}</p>
                                <h3 class="mt-2 text-lg font-bold text-slate-900">{{ $relatedCourse->title }}</h3>
                                <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ \Illuminate\Support\Str::limit($relatedCourse->overview, 120) }}</p>
                                <a href="{{ route('landing.courses.show', ['course' => $relatedCourse->id, 'slug' => $relatedCourse->seo_slug]) }}" class="mt-4 inline-flex items-center rounded-full bg-[#0a2d27] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#11443c]">View Course</a>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </main>

    <footer class="bg-white border-t border-slate-200 py-12 lg:py-16">
        <div class="mx-auto max-w-6xl px-6 lg:px-8 text-center lg:text-left">
            <div class="grid gap-10 lg:grid-cols-[1.4fr_1fr_1fr]">
                <div>
                    <div class="flex items-center justify-center gap-3 lg:justify-start">
                        <img src="{{ asset('images/logos/green.png') }}" alt="think.er HUB logo" class="h-8 w-auto">
                    </div>
                    <p class="mt-4 max-w-sm text-sm leading-relaxed text-slate-500">
                        think.er HUB connects tutors who run curated courses with learners who enroll to build practical skills.
                    </p>
                    <div class="mt-6 flex flex-wrap items-center justify-center gap-4 text-sm text-slate-500 lg:justify-start">
                        <a href="{{ route('login') }}" class="inline-flex items-center rounded-full bg-[#0a2d27] px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-[#11443c]">Login</a>
                    </div>
                </div>

                <div class="hidden lg:block">
                    <h3 class="text-sm font-bold text-slate-900">Menu</h3>
                    <ul class="mt-4 space-y-2.5 text-sm text-slate-500">
                        <li><a href="{{ route('home') }}" class="transition hover:text-[#0a2d27]">Home</a></li>
                        <li><a href="{{ route('landing.courses') }}" class="transition hover:text-[#0a2d27]">Courses</a></li>
                        <li><a href="{{ route('hub.index') }}" class="transition hover:text-[#0a2d27]">Knowledge Hub</a></li>
                        <li><a href="{{ route('landing.instructors') }}" class="transition hover:text-[#0a2d27]">Network</a></li>
                        <li><a href="{{ route('landing.contact') }}" class="transition hover:text-[#0a2d27]">Contact</a></li>
                        @auth
                            <li><a href="{{ route('dashboard') }}" class="transition hover:text-[#0a2d27]">Login</a></li>
                        @else
                            <li><a href="{{ route('login') }}" class="transition hover:text-[#0a2d27]">Login</a></li>
                        @endauth
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-slate-900">Contacts</h3>
                    <div class="mt-4 space-y-2.5 text-sm text-slate-500">
                        <p><span class="font-semibold text-slate-700">Phone:</span> <button type="button" @click="$dispatch('open-contact')" class="ml-1 text-[#0a2d27] font-medium underline-offset-2 hover:underline cursor-pointer">+260772640546</button></p>
                        <p><span class="font-semibold text-slate-700">Email:</span> <a href="mailto:thinkerhub@oristudiozm.com" class="text-[#0a2d27] underline-offset-2 hover:underline">thinkerhub@oristudiozm.com</a></p>
                        <p><span class="font-semibold text-slate-700">Address:</span> 10A Off Natwange Street, Airpot, Livingstone Zambia</p>
                    </div>
                    <div class="mt-4 flex items-center justify-center gap-4 text-slate-500 lg:justify-start">
                        <a href="#" class="transition hover:text-[#0a2d27]" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" class="transition hover:text-[#0a2d27]" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
                        <a href="#" class="transition hover:text-[#0a2d27]" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                    </div>
                </div>
            </div>

            <div class="mt-8 border-t border-slate-200 pt-5">
                <div class="flex flex-col items-center gap-4 text-center text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:text-left">
                    <p>© {{ now()->year }} Thinker Hub. All rights reserved.</p>
                    <div class="flex flex-wrap items-center gap-4">
                        <a href="{{ route('landing.privacy') }}" class="underline-offset-4 hover:text-slate-700 hover:underline">Privacy</a>
                        <a href="{{ route('landing.cookies') }}" class="underline-offset-4 hover:text-slate-700 hover:underline">Cookies</a>
                        <a href="{{ route('landing.terms') }}" class="underline-offset-4 hover:text-slate-700 hover:underline">T&amp;Cs</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

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
    @include('partials.legal-modals')
</body>
</html>