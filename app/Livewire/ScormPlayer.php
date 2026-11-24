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
        return view('livewire.scorm-player', [
            'launchUrl' => $this->package->launch_url,
            'commitUrl' => $this->attempt ? route('scorm.commit', $this->attempt) : null,
            'xapiUrl' => $this->attempt ? route('scorm.xapi', $this->attempt) : null,
        ]);
    }
}
