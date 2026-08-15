<?php

namespace App\Filament\Student\Pages;

use App\Models\Enrollment;
use Filament\Pages\Page;

class Certificates extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-trophy';

    protected static string|\UnitEnum|null $navigationGroup = 'LEARNING';

    protected static ?string $navigationLabel = 'Certificates';

    protected static ?int $navigationSort = 5;

    protected static ?string $title = 'My Certificates';

    protected string $view = 'filament.student.pages.certificates';

    public array $certificates = [];

    public function mount(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $completedCourseIds = Enrollment::query()
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->pluck('course_id')
            ->all();

        $this->certificates = $user->certificates()
            ->whereIn('course_id', $completedCourseIds)
            ->with('course')
            ->latest('issued_at')
            ->get()
            ->map(fn ($certificate): array => [
                'id' => $certificate->id,
                'course_title' => $certificate->course?->title ?? 'Course',
                'course_code' => $certificate->course?->code ?? '',
                'issued_at' => $certificate->issued_at?->format('M d, Y'),
                'verification_code' => $certificate->verification_code,
                'verification_url' => $certificate->verificationUrl(),
                'download_url' => route('certificates.download', $certificate),
            ])
            ->all();
    }
}
