<?php

namespace App\Livewire\Public;

use App\Models\HubPost;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class HubIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $type = 'all';

    public string $category = 'all';

    public ?int $activePostId = null;

    public ?int $activeVideoId = null;

    public bool $showSubmitModal = false;
    public string $submitTitle = '';
    public string $submitType = 'blog';
    public string $submitCategory = 'General';
    public string $submitExcerpt = '';
    public string $submitContent = '';
    public string $submitYoutubeUrl = '';
    public string $submitOpportunityLink = '';
    public ?string $submitOpportunityDeadline = null;
    public ?string $submitNoticeMessage = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'type' => ['except' => 'all'],
        'category' => ['except' => 'all'],
    ];

    public function openSubmitModal(): void
    {
        $this->resetSubmitForm();
        $this->showSubmitModal = true;
    }

    public function closeSubmitModal(): void
    {
        $this->showSubmitModal = false;
        $this->resetSubmitForm();
    }

    public function resetSubmitForm(): void
    {
        $this->submitTitle = '';
        $this->submitType = 'blog';
        $this->submitCategory = 'General';
        $this->submitExcerpt = '';
        $this->submitContent = '';
        $this->submitYoutubeUrl = '';
        $this->submitOpportunityLink = '';
        $this->submitOpportunityDeadline = null;
    }

    public function submitResource(): void
    {
        $this->validate([
            'submitTitle' => 'required|string|max:255',
            'submitType' => 'required|string|in:video,tip_trick,blog,opportunity',
            'submitCategory' => 'required|string|max:255',
            'submitExcerpt' => 'nullable|string|max:500',
            'submitContent' => 'nullable|string',
            'submitYoutubeUrl' => 'nullable|url|max:255',
            'submitOpportunityLink' => 'nullable|url|max:255',
            'submitOpportunityDeadline' => 'nullable|date',
        ]);

        $isAdmin = auth()->user()?->isAdmin() ?? false;
        $isPublished = $isAdmin;

        HubPost::create([
            'title' => $this->submitTitle,
            'type' => $this->submitType,
            'category' => $this->submitCategory,
            'excerpt' => $this->submitExcerpt,
            'content' => $this->submitContent,
            'youtube_url' => $this->submitYoutubeUrl,
            'opportunity_link' => $this->submitOpportunityLink,
            'opportunity_deadline' => $this->submitOpportunityDeadline ?: null,
            'is_published' => $isPublished,
            'author_id' => auth()->id() ?? 1,
        ]);

        if ($isPublished) {
            $this->submitNoticeMessage = 'Your resource has been published to the Knowledge Hub!';
        } else {
            $this->submitNoticeMessage = 'Thank you! Your submission has been received and will be published once approved by an Admin.';
        }

        $this->closeSubmitModal();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingType(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function selectType(string $type): void
    {
        $this->type = $type;
        $this->resetPage();
    }

    public function selectCategory(string $category): void
    {
        $this->category = $category;
        $this->resetPage();
    }

    public function resetSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->type = 'all';
        $this->category = 'all';
        $this->resetPage();
    }

    public function openPostModal(int $id): void
    {
        $this->activePostId = $id;
    }

    public function closePostModal(): void
    {
        $this->activePostId = null;
    }

    public function openVideoModal(int $id): void
    {
        $this->activeVideoId = $id;
    }

    public function closeVideoModal(): void
    {
        $this->activeVideoId = null;
    }

    public function render(): View
    {
        $query = HubPost::query()
            ->published()
            ->with('author:id,name,profile_photo_path')
            ->search($this->search)
            ->type($this->type)
            ->category($this->category)
            ->orderByRaw("CASE WHEN type = 'video' THEN 1 WHEN type = 'tip_trick' THEN 2 WHEN type = 'blog' THEN 3 ELSE 4 END")
            ->latest();

        $posts = $query->paginate(12);

        $categories = HubPost::query()
            ->published()
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->toArray();

        sort($categories);

        $activePost = $this->activePostId ? HubPost::with('author')->find($this->activePostId) : null;
        $activeVideo = $this->activeVideoId ? HubPost::with('author')->find($this->activeVideoId) : null;

        return view('livewire.public.hub-index', [
            'posts' => $posts,
            'categories' => $categories,
            'activePost' => $activePost,
            'activeVideo' => $activeVideo,
        ])->layout('layouts.public', ['title' => 'Knowledge & Opportunities Hub']);
    }
}
