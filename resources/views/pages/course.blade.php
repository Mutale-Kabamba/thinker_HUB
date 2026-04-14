<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo-meta', [
        'title' => $course->title.' | think.er HUB',
        'description' => $course->overview ?: ($course->description ?: 'Practical, career-focused course at think.er HUB.'),
        'keywords' => strtolower(($course->code ? $course->code.', ' : '').'thinker hub, digital skills, practical training'),
        'type' => 'article',
    ])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.pwa-register')
</head>
<body class="bg-[#f8fcf9] text-slate-900 font-sans antialiased" x-data="{ mobileMenu: false }">

    <header class="sticky top-0 z-50 bg-[#0a2d27] py-4 shadow-lg">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-bold text-white shrink-0">
                <img src="{{ asset('images/logos/yellow_white.png') }}" alt="think.er HUB logo" class="h-8 w-auto">
            </a>

            <nav class="hidden md:flex items-center gap-10 text-[13px] font-semibold uppercase tracking-wider text-slate-300">
                <a href="{{ route('home') }}" class="hover:text-yellow-400 transition-colors">Home</a>
                <a href="{{ route('landing.courses') }}" class="text-yellow-400">Courses</a>
                <a href="{{ route('landing.instructors') }}" class="hover:text-yellow-400 transition-colors">Instructors</a>
                <a href="{{ route('landing.contact') }}" class="hover:text-yellow-400 transition-colors">Contact</a>
            </nav>

            <div class="hidden md:flex items-center gap-6">
                <a href="{{ route('login') }}" class="text-sm font-bold text-white hover:text-yellow-400">Login</a>
                <a href="{{ route('enroll') }}" class="rounded-full bg-yellow-400 px-6 py-2.5 text-sm font-bold text-[#0a2d27] hover:bg-white transition-all">Enroll Now</a>
            </div>

            <button class="md:hidden text-white text-2xl" @click="mobileMenu = !mobileMenu">
                <i class="fa-solid" :class="mobileMenu ? 'fa-xmark' : 'fa-bars-staggered'"></i>
            </button>
        </div>

        <div class="md:hidden bg-[#0a2d27] border-t border-white/10" x-show="mobileMenu" x-transition>
            <nav class="flex flex-col p-6 gap-4 text-white font-semibold">
                <a href="{{ route('home') }}">Home</a>
                <a href="{{ route('landing.courses') }}" class="text-yellow-400">Courses</a>
                <a href="{{ route('landing.instructors') }}">Instructors</a>
                <a href="{{ route('landing.contact') }}">Contact</a>
            </nav>
        </div>
    </header>

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

                $progressionCards[] = $matched ?? [
                    'level' => $levelName,
                    'details' => 'Details coming soon.',
                ];
            }
        @endphp

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
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-yellow-400">{{ $course->code }}</p>
                <h1 class="mt-4 max-w-4xl text-4xl font-black text-white sm:text-5xl">{{ $course->title }}</h1>
                <p class="mt-5 max-w-3xl text-slate-300">{{ $course->overview ?: $course->description }}</p>
                <div class="mt-8">
                    <a href="{{ route('landing.courses') }}" class="inline-flex items-center gap-2 rounded-full border border-white/20 px-5 py-2.5 text-sm font-semibold text-white hover:bg-white/10">
                        <i class="fa-solid fa-arrow-left"></i>
                        Back to Courses
                    </a>
                </div>
            </div>
        </section>

        <section class="py-16 lg:py-20">
            <div class="mx-auto grid max-w-6xl gap-10 px-6 lg:grid-cols-3 lg:px-8">
                <article class="lg:col-span-2">
                    <h2 class="text-xl font-bold text-slate-900">Course Overview</h2>
                    <p class="mt-3 leading-relaxed text-slate-600">{{ $course->overview ?: 'Overview coming soon.' }}</p>

                    <h3 class="mt-8 text-lg font-bold text-slate-900">Key Outcome</h3>
                    <p class="mt-3 leading-relaxed text-slate-600">{{ $course->key_outcome ?: 'Key outcomes will be shared soon.' }}</p>

                    <h3 class="mt-8 text-lg font-bold text-slate-900">Fees</h3>
                    @if (! empty($feeSections))
                        <div class="mt-3 space-y-5">
                            @foreach ($feeSections as $section)
                                <section class="border-t border-slate-200 pt-4 first:border-t-0 first:pt-0">
                                    <h4 class="text-sm font-semibold text-slate-800">{{ $section['label'] }}</h4>
                                    <div class="mt-2 space-y-2">
                                        @foreach ($section['rows'] as $row)
                                            <div class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2">
                                                <span class="text-sm font-medium text-slate-800">{{ $row['level'] !== '' ? $row['level'] : '-' }}</span>
                                                <span class="text-sm font-semibold text-slate-900">{{ $row['amount'] !== '' ? $row['amount'] : '-' }}</span>
                                                <span class="text-sm text-slate-600">{{ $row['duration'] !== '' ? $row['duration'] : '-' }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </section>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-3 leading-relaxed text-slate-600">No fee details added yet.</p>
                    @endif

                    <h3 class="mt-8 text-lg font-bold text-slate-900">Levels &amp; Progression</h3>
                    @if (! empty($progressionCards))
                        <div class="mt-3 space-y-3">
                            @foreach ($progressionCards as $item)
                                <section class="border-t border-slate-200 pt-3 first:border-t-0 first:pt-0">
                                    <h4 class="text-base font-semibold text-slate-800">{{ $item['level'] !== '' ? $item['level'] : 'Level' }}</h4>
                                    <p class="mt-1 text-sm leading-relaxed text-slate-600">{{ $item['details'] !== '' ? $item['details'] : 'Details coming soon.' }}</p>
                                </section>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-3 leading-relaxed text-slate-600">No progression details added yet.</p>
                    @endif
                </article>

                <aside class="border-t border-slate-200 pt-6 lg:border-l lg:border-t-0 lg:pl-6 lg:pt-0">
                    <h2 class="text-lg font-bold text-slate-900">Quick Facts</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div>
                            <dt class="font-semibold text-slate-500">Code</dt>
                            <dd class="mt-1 font-bold text-slate-900">{{ $course->code }}</dd>
                        </div>
                        <div class="border-t border-slate-200 pt-3">
                            <dt class="font-semibold text-slate-500">Timeline</dt>
                            <dd class="mt-1 font-bold text-slate-900">{{ $course->timeline ?: 'Self paced' }}</dd>
                        </div>
                    </dl>
                    <a href="{{ route('enroll') }}" class="mt-6 inline-flex w-full items-center justify-center rounded-full bg-yellow-400 px-5 py-3 text-sm font-bold text-[#0a2d27] hover:bg-yellow-300">Enroll in This Track</a>
                </aside>
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
                        Thinker Hub empowers learners with practical, career-focused training designed to turn knowledge into measurable results.
                    </p>
                </div>

                <div class="hidden lg:block">
                    <h3 class="text-sm font-bold text-slate-900">Menu</h3>
                    <ul class="mt-4 space-y-2.5 text-sm text-slate-500">
                        <li><a href="{{ route('home') }}" class="transition hover:text-[#0a2d27]">Home</a></li>
                        <li><a href="{{ route('landing.courses') }}" class="transition hover:text-[#0a2d27]">Courses</a></li>
                        <li><a href="{{ route('landing.instructors') }}" class="transition hover:text-[#0a2d27]">Instructors</a></li>
                        <li><a href="{{ route('landing.contact') }}" class="transition hover:text-[#0a2d27]">Contact</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-slate-900">Contacts</h3>
                    <div class="mt-4 space-y-2.5 text-sm text-slate-500">
                        <div class="relative" x-data="{ phoneMenu: false }">
                            <span class="font-semibold text-slate-700">Phone:</span>
                            <button type="button" @click="phoneMenu = !phoneMenu" class="ml-1 text-[#0a2d27] underline-offset-2 hover:underline">+260772640546</button>
                            <div x-show="phoneMenu" x-transition @click.outside="phoneMenu = false" class="absolute left-0 z-20 mt-2 w-44 rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg" style="display: none;">
                                <a href="tel:+260772640546" class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"><i class="fa-solid fa-phone text-teal-600"></i>Call</a>
                                <a href="https://wa.me/260772640546" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"><i class="fa-brands fa-whatsapp text-green-600"></i>WhatsApp</a>
                            </div>
                        </div>
                        <p><span class="font-semibold text-slate-700">Email:</span> <a href="mailto:thinker.learn@gmail.com" class="text-[#0a2d27] underline-offset-2 hover:underline">thinker.learn@gmail.com</a></p>
                        <p><span class="font-semibold text-slate-700">Address:</span> 10A Off Natwange Street, Airpot, Livingstone Zambia</p>
                    </div>
                </div>
            </div>

            <div class="mt-8 border-t border-slate-200 pt-5 text-xs text-slate-500">
                <p>© {{ now()->year }} Thinker Hub. All rights reserved.</p>
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
