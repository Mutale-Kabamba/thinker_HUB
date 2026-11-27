<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\ScormAttempt;
use App\Models\ScormPackage;
use App\Models\ScormTracking;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class ScormPackageService
{
    /**
     * Validate, extract (zip-slip safe), and register a SCORM 1.2 / 2004 or xAPI (.zip) package.
     *
     * @param  array{title?: ?string, course_id?: ?int, lesson_id?: ?int, tenant_id?: ?int, disk?: string}  $attributes
     */
    public function importZip(string $zipAbsolutePath, array $attributes = []): ScormPackage
    {
        if (! is_file($zipAbsolutePath)) {
            throw new InvalidArgumentException('Uploaded SCORM/xAPI archive file could not be found.');
        }

        $zip = new ZipArchive;
        if ($zip->open($zipAbsolutePath) !== true) {
            throw new InvalidArgumentException('Unable to open uploaded ZIP archive.');
        }

        $manifestInfo = null;
        $manifestPrefix = '';

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = str_replace('\\', '/', (string) $zip->getNameIndex($i));
            if (str_contains($entryName, '..') || str_starts_with($entryName, '/')) {
                $zip->close();
                throw new InvalidArgumentException('Unsafe file path detected inside archive.');
            }

            $base = strtolower(basename($entryName));
            if ($base === 'imsmanifest.xml' || $base === 'tincan.xml') {
                $xmlContent = $zip->getFromIndex($i);
                if (is_string($xmlContent) && trim($xmlContent) !== '') {
                    $manifestInfo = $base === 'imsmanifest.xml'
                        ? $this->parseImsManifest($xmlContent)
                        : $this->parseTinCanManifest($xmlContent);
                    $dir = dirname($entryName);
                    $manifestPrefix = ($dir === '.' || $dir === '') ? '' : trim($dir, '/') . '/';
                    break;
                }
            }
        }

        if ($manifestInfo === null) {
            $zip->close();
            throw new InvalidArgumentException('Invalid SCORM/xAPI package: neither imsmanifest.xml nor tincan.xml was found.');
        }

        $diskName = (string) ($attributes['disk'] ?? 'public');
        $packageUuid = (string) Str::uuid();
        $relativeBaseDir = 'scorm/' . $packageUuid;
        $disk = Storage::disk($diskName);

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = str_replace('\\', '/', (string) $zip->getNameIndex($i));
            if ($entryName === '' || str_ends_with($entryName, '/')) {
                continue;
            }
            if (str_contains($entryName, '..') || str_starts_with($entryName, '/')) {
                continue;
            }

            $stream = $zip->getStream($zip->getNameIndex($i));
            if ($stream === false) {
                continue;
            }

            $targetRelative = $relativeBaseDir . '/' . ltrim($entryName, '/');
            $disk->put($targetRelative, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        $zip->close();

        $entryPoint = ltrim($manifestPrefix . ltrim($manifestInfo['entry_point'], '/'), '/');

        $package = ScormPackage::create([
            'tenant_id' => $attributes['tenant_id'] ?? null,
            'course_id' => $attributes['course_id'] ?? null,
            'lesson_id' => $attributes['lesson_id'] ?? null,
            'title' => trim((string) (($attributes['title'] ?? null) ?: ($manifestInfo['title'] ?? null) ?: 'SCORM Package')),
            'standard' => $manifestInfo['standard'],
            'version' => $manifestInfo['version'],
            'storage_disk' => $diskName,
            'storage_path' => $relativeBaseDir,
            'entry_point' => $entryPoint,
            'manifest_identifier' => $manifestInfo['identifier'],
            'manifest_meta' => $manifestInfo['meta'],
            'is_active' => true,
        ]);

        // Auto-provision published SCORM lesson(s) in the course so they appear immediately in Course Content
        if ($package->course_id && ! $package->lesson_id) {
            $manifestItems = $manifestInfo['meta']['items'] ?? [];

            if (is_array($manifestItems) && count($manifestItems) > 1) {
                $nextOrder = ((int) Lesson::where('course_id', $package->course_id)->max('order_column')) + 1;
                $nextSectionOrder = ((int) \App\Models\CourseSection::where('course_id', $package->course_id)->max('sort_order')) + 1;
                $sectionCache = [];
                $createdSectionIds = [];
                $lessonEntries = [];
                $firstLessonId = null;

                foreach ($manifestItems as $item) {
                    $sectionTitle = trim((string) ($item['section'] ?? ''));
                    $sectionId = null;

                    if ($sectionTitle !== '') {
                        if (! isset($sectionCache[$sectionTitle])) {
                            $section = \App\Models\CourseSection::create([
                                'course_id' => $package->course_id,
                                'title' => $sectionTitle,
                                'sort_order' => $nextSectionOrder++,
                            ]);
                            $sectionCache[$sectionTitle] = $section->id;
                            $createdSectionIds[] = $section->id;
                        }
                        $sectionId = $sectionCache[$sectionTitle];
                    }

                    $lesson = Lesson::create([
                        'course_id' => $package->course_id,
                        'course_section_id' => $sectionId,
                        'title' => trim((string) ($item['title'] ?? $package->title)),
                        'type' => 'scorm',
                        'scorm_package_id' => $package->id,
                        'order_column' => $nextOrder,
                        'sort_order' => $nextOrder,
                        'is_published' => true,
                    ]);
                    $nextOrder++;

                    if ($firstLessonId === null) {
                        $firstLessonId = $lesson->id;
                    }

                    $itemHref = ltrim($manifestPrefix . ltrim((string) ($item['href'] ?? $manifestInfo['entry_point']), '/'), '/');
                    $lessonEntries[(string) $lesson->id] = $itemHref;
                }

                $meta = is_array($package->manifest_meta) ? $package->manifest_meta : [];
                $meta['lesson_entries'] = $lessonEntries;
                $meta['section_ids'] = $createdSectionIds;

                $package->update([
                    'lesson_id' => $firstLessonId,
                    'manifest_meta' => $meta,
                ]);
            } else {
                $nextOrder = ((int) Lesson::where('course_id', $package->course_id)->max('order_column')) + 1;
                $lesson = Lesson::create([
                    'course_id' => $package->course_id,
                    'title' => $package->title,
                    'type' => 'scorm',
                    'scorm_package_id' => $package->id,
                    'order_column' => $nextOrder,
                    'sort_order' => $nextOrder,
                    'is_published' => true,
                ]);
                $package->update(['lesson_id' => $lesson->id]);
            }
        }

        return $package->fresh();
    }

    /**
     * Start or resume the latest SCORM/xAPI attempt for a learner.
     */
    public function startOrResumeAttempt(ScormPackage $package, User $user, ?Lesson $lesson = null): ScormAttempt
    {
        $resolvedLessonId = $lesson?->id ?? $package->lesson_id;

        $query = ScormAttempt::query()
            ->where('scorm_package_id', $package->id)
            ->where('user_id', $user->id);

        if ($resolvedLessonId) {
            $query->where('lesson_id', $resolvedLessonId);
        }

        $existing = $query->latest('attempt_number')->first();

        if ($existing) {
            return $existing;
        }

        return ScormAttempt::create([
            'scorm_package_id' => $package->id,
            'user_id' => $user->id,
            'lesson_id' => $resolvedLessonId,
            'tenant_id' => $package->tenant_id ?? $user->tenant_id,
            'attempt_number' => 1,
            'lesson_status' => 'not attempted',
            'started_at' => now(),
        ]);
    }

    /**
     * Persist SCORM 1.2 / 2004 CMI data elements and advance course progression when completed/passed.
     *
     * @param  array<string, mixed>  $cmiData
     */
    public function commitCmiData(ScormAttempt $attempt, array $cmiData): ScormAttempt
    {
        $updates = [];

        foreach ($cmiData as $element => $value) {
            $elementKey = trim((string) $element);
            if ($elementKey === '') {
                continue;
            }

            $stringValue = is_scalar($value) || $value === null ? (string) $value : json_encode($value);

            ScormTracking::create([
                'scorm_attempt_id' => $attempt->id,
                'element' => mb_substr($elementKey, 0, 190),
                'value' => $stringValue,
                'recorded_at' => now(),
            ]);

            $trimmed = trim($stringValue);

            match ($elementKey) {
                'cmi.core.lesson_status', 'cmi.completion_status', 'cmi.success_status' => $trimmed !== ''
                    ? ($updates['lesson_status'] = strtolower($trimmed))
                    : null,
                'cmi.core.score.raw', 'cmi.score.raw' => is_numeric($trimmed)
                    ? ($updates['score_raw'] = (float) $trimmed)
                    : null,
                'cmi.core.score.min', 'cmi.score.min' => is_numeric($trimmed)
                    ? ($updates['score_min'] = (float) $trimmed)
                    : null,
                'cmi.core.score.max', 'cmi.score.max' => is_numeric($trimmed)
                    ? ($updates['score_max'] = (float) $trimmed)
                    : null,
                'cmi.score.scaled' => is_numeric($trimmed)
                    ? ($updates['score_scaled'] = (float) $trimmed)
                    : null,
                'cmi.core.lesson_location', 'cmi.location' => $trimmed !== ''
                    ? ($updates['lesson_location'] = mb_substr($trimmed, 0, 255))
                    : null,
                'cmi.suspend_data' => $trimmed !== ''
                    ? ($updates['suspend_data'] = $stringValue)
                    : null,
                'cmi.core.session_time', 'cmi.session_time' => $trimmed !== ''
                    ? ($updates['session_time'] = mb_substr($trimmed, 0, 64))
                    : null,
                'cmi.core.total_time', 'cmi.total_time' => $trimmed !== ''
                    ? ($updates['total_time'] = mb_substr($trimmed, 0, 64))
                    : null,
                default => null,
            };
        }

        if (isset($updates['lesson_status']) && in_array($updates['lesson_status'], ['completed', 'passed'], true) && $attempt->completed_at === null) {
            $updates['completed_at'] = now();
        }

        if ($updates !== []) {
            $attempt->update($updates);
        }

        $attempt->refresh();

        if ($attempt->isCompletedOrPassed() && $attempt->lesson_id && $attempt->user) {
            $lesson = Lesson::find($attempt->lesson_id);
            if ($lesson) {
                app(ProgressionService::class)->completeAndAdvance($attempt->user, $lesson);
            }
        }

        return $attempt;
    }

    /**
     * Record an xAPI (Tin Can) statement against a SCORM/xAPI attempt.
     *
     * @param  array<string, mixed>  $statement
     */
    public function recordXapiStatement(ScormAttempt $attempt, array $statement): ScormAttempt
    {
        $verbId = (string) ($statement['verb']['id'] ?? $statement['verb'] ?? 'http://adlnet.gov/expapi/verbs/experienced');
        $verbShort = strtolower(basename(str_replace('\\', '/', $verbId)));

        ScormTracking::create([
            'scorm_attempt_id' => $attempt->id,
            'element' => mb_substr('xapi.' . $verbShort, 0, 190),
            'value' => $verbId,
            'raw_payload' => $statement,
            'recorded_at' => now(),
        ]);

        $updates = [];

        if (in_array($verbShort, ['completed', 'passed', 'failed'], true)) {
            $updates['lesson_status'] = $verbShort;
            if (in_array($verbShort, ['completed', 'passed'], true) && $attempt->completed_at === null) {
                $updates['completed_at'] = now();
            }
        } elseif ($attempt->lesson_status === 'not attempted') {
            $updates['lesson_status'] = 'incomplete';
        }

        $result = $statement['result'] ?? [];
        if (is_array($result)) {
            if (isset($result['score']['raw']) && is_numeric($result['score']['raw'])) {
                $updates['score_raw'] = (float) $result['score']['raw'];
            }
            if (isset($result['score']['scaled']) && is_numeric($result['score']['scaled'])) {
                $updates['score_scaled'] = (float) $result['score']['scaled'];
            }
            if (isset($result['duration'])) {
                $updates['session_time'] = mb_substr((string) $result['duration'], 0, 64);
            }
        }

        if ($updates !== []) {
            $attempt->update($updates);
        }

        $attempt->refresh();

        if ($attempt->isCompletedOrPassed() && $attempt->lesson_id && $attempt->user) {
            $lesson = Lesson::find($attempt->lesson_id);
            if ($lesson) {
                app(ProgressionService::class)->completeAndAdvance($attempt->user, $lesson);
            }
        }

        return $attempt;
    }

    /**
     * @return array{standard: string, version: string, title: string, entry_point: string, identifier: ?string, meta: array<string, mixed>}
     */
    private function parseImsManifest(string $xmlContent): array
    {
        $prev = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlContent);
        libxml_use_internal_errors($prev);

        if (! $xml instanceof SimpleXMLElement) {
            throw new RuntimeException('Failed to parse imsmanifest.xml.');
        }

        $xml->registerXPathNamespace('ims', 'http://www.imsproject.org/xsd/imscp_rootv1p1p2');
        $identifier = (string) ($xml['identifier'] ?? '');
        $rawLower = strtolower($xmlContent);

        $isScorm2004 = str_contains($rawLower, '2004') || str_contains($rawLower, 'adlcp_v1p3');
        $standard = $isScorm2004 ? ScormPackage::STANDARD_SCORM_2004 : ScormPackage::STANDARD_SCORM_12;
        $version = $isScorm2004 ? '2004' : '1.2';

        $titleNodes = $xml->xpath('//*[local-name()="organization"]/*[local-name()="title"]') ?: [];
        $title = ! empty($titleNodes) ? trim((string) $titleNodes[0]) : 'SCORM Module';

        $resourceNodes = $xml->xpath('//*[local-name()="resources"]/*[local-name()="resource"][@href]') ?: [];
        $resourceMap = [];
        foreach ($resourceNodes as $res) {
            $resId = trim((string) ($res['identifier'] ?? ''));
            $resHref = trim((string) ($res['href'] ?? ''));
            if ($resId !== '' && $resHref !== '') {
                $resourceMap[$resId] = $resHref;
            }
        }

        $entryPoint = ! empty($resourceNodes) ? trim((string) $resourceNodes[0]['href']) : 'index.html';

        // Parse multi-SCO organization hierarchy (top-level items = sections or standalone SCOs)
        $parsedItems = [];
        $orgNodes = $xml->xpath('//*[local-name()="organizations"]/*[local-name()="organization"]') ?: [];
        if (! empty($orgNodes)) {
            $topItems = $orgNodes[0]->xpath('./*[local-name()="item"]') ?: [];
            foreach ($topItems as $topItem) {
                $topTitleNodes = $topItem->xpath('./*[local-name()="title"]') ?: [];
                $topTitle = ! empty($topTitleNodes) ? trim((string) $topTitleNodes[0]) : '';
                $topRef = trim((string) ($topItem['identifierref'] ?? ''));
                $childItems = $topItem->xpath('./*[local-name()="item"]') ?: [];

                if (! empty($childItems)) {
                    foreach ($childItems as $child) {
                        $childTitleNodes = $child->xpath('./*[local-name()="title"]') ?: [];
                        $childTitle = ! empty($childTitleNodes) ? trim((string) $childTitleNodes[0]) : '';
                        $childRef = trim((string) ($child['identifierref'] ?? ''));
                        if ($childRef !== '' && isset($resourceMap[$childRef])) {
                            $parsedItems[] = [
                                'section' => $topTitle,
                                'title' => $childTitle ?: $topTitle,
                                'href' => $resourceMap[$childRef],
                            ];
                        }
                    }
                } elseif ($topRef !== '' && isset($resourceMap[$topRef])) {
                    $parsedItems[] = [
                        'section' => '',
                        'title' => $topTitle ?: $title,
                        'href' => $resourceMap[$topRef],
                    ];
                }
            }
        }

        if (! empty($parsedItems)) {
            $entryPoint = $parsedItems[0]['href'];
        }

        return [
            'standard' => $standard,
            'version' => $version,
            'title' => $title ?: 'SCORM Module',
            'entry_point' => $entryPoint ?: 'index.html',
            'identifier' => $identifier !== '' ? $identifier : null,
            'meta' => [
                'manifest_file' => 'imsmanifest.xml',
                'items' => $parsedItems,
            ],
        ];
    }

    /**
     * @return array{standard: string, version: string, title: string, entry_point: string, identifier: ?string, meta: array<string, mixed>}
     */
    private function parseTinCanManifest(string $xmlContent): array
    {
        $prev = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlContent);
        libxml_use_internal_errors($prev);

        if (! $xml instanceof SimpleXMLElement) {
            throw new RuntimeException('Failed to parse tincan.xml.');
        }

        $activityNodes = $xml->xpath('//*[local-name()="activity"]') ?: [];
        $activity = $activityNodes[0] ?? null;

        $identifier = $activity ? trim((string) ($activity['id'] ?? '')) : '';
        $nameNodes = $xml->xpath('//*[local-name()="activity"]/*[local-name()="name"]') ?: [];
        $launchNodes = $xml->xpath('//*[local-name()="activity"]/*[local-name()="launch"]') ?: [];

        $title = ! empty($nameNodes) ? trim((string) $nameNodes[0]) : 'xAPI Module';
        $entryPoint = ! empty($launchNodes) ? trim((string) $launchNodes[0]) : 'index.html';

        return [
            'standard' => ScormPackage::STANDARD_XAPI,
            'version' => '1.0',
            'title' => $title ?: 'xAPI Module',
            'entry_point' => $entryPoint ?: 'index.html',
            'identifier' => $identifier !== '' ? $identifier : null,
            'meta' => [
                'manifest_file' => 'tincan.xml',
            ],
        ];
    }
}
