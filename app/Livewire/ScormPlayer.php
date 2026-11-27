<?php

namespace App\Livewire;

use App\Models\Lesson;
use App\Models\ScormAttempt;
use App\Models\ScormPackage;
use App\Services\ScormPackageService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ScormPlayer extends Component
{
    public ScormPackage $package;
    public ?int $lessonId = null;
    public ?ScormAttempt $attempt = null;

    public function mount(ScormPackage $package, ?int $lessonId = null, ?ScormPackageService $scormService = null): void
    {
        $this->package = $package;
        $this->lessonId = $lessonId ?? $package->lesson_id;

        $user = Auth::user();
        if ($user) {
            $lesson = $this->lessonId ? Lesson::find($this->lessonId) : null;
            $this->attempt = ($scormService ?? app(ScormPackageService::class))
                ->startOrResumeAttempt($package, $user, $lesson);
        }
    }

    public function render()
    {
        $lessonEntries = is_array($this->package->manifest_meta['lesson_entries'] ?? null)
            ? $this->package->manifest_meta['lesson_entries']
            : [];

        $lessonEntryPath = $this->lessonId
            ? ($lessonEntries[(string) $this->lessonId] ?? $lessonEntries[$this->lessonId] ?? null)
            : null;

        $launchUrl = $lessonEntryPath
            ? route('scorm.content', [
                'package' => $this->package->id,
                'path' => ltrim((string) $lessonEntryPath, '/'),
            ])
            : $this->package->launch_url;

        return view('livewire.scorm-player', [
            'launchUrl' => $launchUrl,
            'commitUrl' => $this->attempt ? route('scorm.commit', $this->attempt) : null,
            'xapiUrl' => $this->attempt ? route('scorm.xapi', $this->attempt) : null,
        ]);
    }
}
