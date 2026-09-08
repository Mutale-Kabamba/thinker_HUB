<?php

namespace App\Livewire;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class AssignmentTaskEmbedded extends Component
{
    use WithFileUploads;

    public int $assignmentId;
    public ?Assignment $assignment = null;
    public string $content = '';
    public string $link = '';
    public $file = null;
    public bool $isSubmitted = false;
    public ?AssignmentSubmission $submission = null;

    public function mount(int $assignmentId)
    {
        $this->assignmentId = $assignmentId;
        $this->assignment = Assignment::find($assignmentId);

        $userId = Auth::id();
        if ($userId) {
            $this->submission = AssignmentSubmission::where('assignment_id', $assignmentId)
                ->where('user_id', $userId)
                ->latest()
                ->first();

            if ($this->submission) {
                $this->isSubmitted = true;
                $this->content = $this->submission->content ?? '';
                $this->link = $this->submission->link ?? '';
            }
        }
    }

    public function submitTask()
    {
        $this->validate([
            'content' => 'nullable|string',
            'link' => 'nullable|url',
            'file' => 'nullable|file|max:20480', // 20MB max
        ]);

        $userId = Auth::id();
        if (! $userId || ! $this->assignment) {
            return;
        }

        $filePath = null;
        if ($this->file) {
            $filePath = $this->file->store('assignment-submissions', 'public');
        }

        $this->submission = AssignmentSubmission::create([
            'assignment_id' => $this->assignment->id,
            'user_id' => $userId,
            'content' => $this->content ?: 'Task submitted via course player.',
            'link' => $this->link ?: null,
            'file_path' => $filePath,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $this->isSubmitted = true;

        $this->dispatch('taskSubmitted');
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Assignment submitted successfully! Next lesson unlocked.',
        ]);
    }

    public function render()
    {
        return view('livewire.assignment-task-embedded');
    }
}
