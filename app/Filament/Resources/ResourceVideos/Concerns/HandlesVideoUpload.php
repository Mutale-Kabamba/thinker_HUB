<?php

namespace App\Filament\Resources\ResourceVideos\Concerns;

use App\Services\VideoMediaService;

/**
 * Shared create/edit handling for the ResourceVideo form's local-upload
 * branch, used by both the Admin and Instructor resource pages. Pulls the
 * non-model form fields (video_source / video_file) out of the data before
 * the record is written, then syncs the Media attachment afterwards.
 */
trait HandlesVideoUpload
{
    /**
     * @var array{source: string, file: string|null, original: string|null}|null
     */
    protected ?array $videoUploadPayload = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->pullVideoUpload($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->pullVideoUpload($data);
    }

    protected function afterCreate(): void
    {
        $this->syncVideoUpload();
    }

    protected function afterSave(): void
    {
        $this->syncVideoUpload();
    }

    /**
     * Strip the upload-only fields from the record payload. The upload
     * branch has no YouTube URL, so youtube_url is blanked (the column is
     * NOT NULL; an empty string marks "no YouTube source" and a Media row
     * marks "local upload" — see ResourceVideo::media()).
     */
    private function pullVideoUpload(array $data): array
    {
        $original = $data['video_file_original_name'] ?? null;

        if (is_array($original)) {
            $original = reset($original) ?: null;
        }

        $file = $data['video_file'] ?? null;

        if (is_array($file)) {
            $file = reset($file) ?: null;
        }

        $this->videoUploadPayload = [
            'source' => $data['video_source'] ?? 'youtube',
            'file' => $file,
            'original' => $original,
        ];

        unset($data['video_source'], $data['video_file'], $data['video_file_original_name']);

        if ($this->videoUploadPayload['source'] === 'upload') {
            $data['youtube_url'] = '';
        }

        return $data;
    }

    private function syncVideoUpload(): void
    {
        if (! $this->record || ! $this->videoUploadPayload) {
            return;
        }

        $service = app(VideoMediaService::class);

        if ($this->videoUploadPayload['source'] === 'upload') {
            // A newly uploaded file replaces any existing local video; no
            // new file on edit keeps the current attachment as-is.
            if ($this->videoUploadPayload['file']) {
                $service->attachLocalVideo(
                    $this->record,
                    $this->videoUploadPayload['file'],
                    $this->videoUploadPayload['original'],
                );
            }

            return;
        }

        // YouTube branch: any previous local upload is removed.
        $service->clearLocalVideo($this->record);
    }
}
