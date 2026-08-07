<?php

namespace App\Livewire\Public;

use App\Models\HubPost;
use App\Models\Media;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class HubIndex extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $search = '';

    public string $type = 'all';

    public string $category = 'all';

    public ?int $activePostId = null;

    public ?int $activeVideoId = null;

    // Modals visibility
    public bool $showSubmitModal = false;
    public bool $showRegisterModal = false;

    // Contributor Registration Form
    public string $regName = '';
    public string $regEmail = '';
    public string $regRole = 'blogger'; // blogger | researcher | employer
    public string $regPassword = '';
    public string $regPasswordConfirmation = '';
    public string $regBio = '';
    public string $regWhatsapp = '';
    public string $regLinkedinUrl = '';
    public string $regFacebookUrl = '';
    public string $regPortfolioUrl = '';
    public string $regGithubUrl = '';
    public string $regInstagramUrl = '';
    public string $regCompany = '';
    public string $regSpecialty = '';

    // Resource Submission form attributes
    public string $submitTitle = '';
    public string $submitType = 'blog';
    public string $submitCategory = 'General';
    public string $submitExcerpt = '';
    public string $submitContent = '';
    public string $submitCodeSnippet = '';
    public string $submitProTip = '';
    public string $submitYoutubeUrl = '';
    public string $submitOpportunityLink = '';
    public ?string $submitOpportunityDeadline = null;

    // Opportunity specific fields
    public string $submitProvider = '';
    public string $submitOpportunityType = 'Job';
    public string $submitLocation = 'Remote';
    public string $submitCompensation = '';
    public string $submitRequirements = '';

    // File Attachments
    public array $submitFiles = [];

    public ?string $submitNoticeMessage = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'type' => ['except' => 'all'],
        'category' => ['except' => 'all'],
    ];

    public function mount(): void
    {
        if (request()->boolean('register') || request()->query('action') === 'register' || request()->boolean('registerModal')) {
            $this->openRegisterModal();
        }
    }


    public function openSubmitModal(): void
    {
        if (! auth()->check()) {
            $this->openRegisterModal();
            return;
        }

        $user = auth()->user();

        if ($user->role === 'student') {
            $this->submitNoticeMessage = 'You are currently logged in as a Student. To publish resources, please register for a Blogger, Researcher, or Employer contributor role.';
            $this->openRegisterModal();
            return;
        }

        if (! $user->is_active && ! $user->isAdmin()) {
            $this->submitNoticeMessage = 'Your contributor account is currently pending Admin approval. You will be able to submit once approved.';
            return;
        }

        // Auto-select type according to user role permissions
        if ($user->isBlogger()) {
            $this->submitType = 'blog';
        } elseif ($user->isResearcher()) {
            $this->submitType = 'tip_trick';
        } elseif ($user->isEmployer()) {
            $this->submitType = 'opportunity';
        } elseif ($user->isInstructor()) {
            $this->submitType = 'video';
        }

        $this->resetSubmitForm();
        $this->showSubmitModal = true;
    }

    public function closeSubmitModal(): void
    {
        $this->showSubmitModal = false;
        $this->resetSubmitForm();
    }

    public function openRegisterModal(): void
    {
        $this->regName = '';
        $this->regEmail = '';
        $this->regRole = 'blogger';
        $this->regPassword = '';
        $this->regPasswordConfirmation = '';
        $this->regBio = '';
        $this->regWhatsapp = '';
        $this->regLinkedinUrl = '';
        $this->regPortfolioUrl = '';
        $this->regGithubUrl = '';
        $this->regCompany = '';
        $this->regSpecialty = '';
        $this->showRegisterModal = true;
    }

    public function closeRegisterModal(): void
    {
        $this->showRegisterModal = false;
    }

    public function registerContributor(): void
    {
        $this->validate([
            'regName' => 'required|string|max:255',
            'regEmail' => 'required|string|email|max:255|unique:users,email',
            'regRole' => 'required|string|in:blogger,researcher,employer',
            'regPassword' => 'required|string|min:8|same:regPasswordConfirmation',
            'regBio' => 'nullable|string|max:1000',
            'regWhatsapp' => 'nullable|string|max:50',
            'regLinkedinUrl' => 'nullable|url|max:255',
            'regPortfolioUrl' => 'nullable|url|max:255',
            'regGithubUrl' => 'nullable|url|max:255',
            'regCompany' => 'nullable|string|max:255',
            'regSpecialty' => 'nullable|string|max:255',
        ]);

        $roleTitle = match ($this->regRole) {
            'blogger' => 'Blogger (Short Blogs)',
            'researcher' => 'Researcher (Tips & Tricks)',
            'employer' => 'Employer (Opportunities)',
            default => ucfirst($this->regRole),
        };

        User::create([
            'name' => $this->regName,
            'email' => $this->regEmail,
            'role' => $this->regRole,
            'is_active' => false, // Pending Admin approval!
            'password' => Hash::make($this->regPassword),
            'bio' => $this->regBio ?: null,
            'whatsapp' => $this->regWhatsapp ?: null,
            'linkedin_url' => $this->regLinkedinUrl ?: null,
            'facebook_url' => $this->regFacebookUrl ?: null,
            'portfolio_url' => $this->regPortfolioUrl ?: null,
            'github_url' => $this->regGithubUrl ?: null,
            'instagram_url' => $this->regInstagramUrl ?: null,
            'company' => $this->regCompany ?: null,
            'specialty' => $this->regSpecialty ?: null,
        ]);

        $this->submitNoticeMessage = "Registration received! Your request for an approved {$roleTitle} profile has been submitted to Admin for review. Once approved, your account will be activated and listed on our Knowledge Network directory.";
        $this->closeRegisterModal();
    }

    public function resetSubmitForm(): void
    {
        $user = auth()->user();
        if ($user?->isBlogger()) {
            $this->submitType = 'blog';
        } elseif ($user?->isResearcher()) {
            $this->submitType = 'tip_trick';
        } elseif ($user?->isEmployer()) {
            $this->submitType = 'opportunity';
        } elseif ($user?->isInstructor()) {
            $this->submitType = 'video';
        } else {
            $this->submitType = 'blog';
        }

        $this->submitTitle = '';
        $this->submitCategory = 'General';
        $this->submitExcerpt = '';
        $this->submitContent = '';
        $this->submitCodeSnippet = '';
        $this->submitProTip = '';
        $this->submitYoutubeUrl = '';
        $this->submitOpportunityLink = '';
        $this->submitOpportunityDeadline = null;
        $this->submitProvider = '';
        $this->submitOpportunityType = 'Job';
        $this->submitLocation = 'Remote';
        $this->submitCompensation = '';
        $this->submitRequirements = '';
        $this->submitFiles = [];
    }

    public function setSubmitType(string $type): void
    {
        $user = auth()->user();
        if ($user && ! $user->canSubmitType($type)) {
            $this->addError('submitType', "Your account role ({$user->role}) does not have permission to submit {$type} resources.");
            return;
        }

        $this->submitType = $type;
    }

    public function submitResource(): void
    {
        $user = auth()->user();
        if (! $user) {
            $this->openRegisterModal();
            return;
        }

        if (! $user->canSubmitType($this->submitType)) {
            $this->addError('submitType', "Your account role ({$user->role}) is not authorized to submit {$this->submitType} posts.");
            return;
        }

        $isAdmin = $user->isAdmin();
        $isPublished = $isAdmin;

        if ($this->submitType === 'opportunity') {
            $this->validate([
                'submitTitle' => 'required|string|max:255',
                'submitProvider' => 'required|string|max:255',
                'submitOpportunityType' => 'required|string|max:255',
                'submitOpportunityLink' => 'nullable|url|max:255',
                'submitOpportunityDeadline' => 'nullable|date',
                'submitLocation' => 'nullable|string|max:255',
                'submitCompensation' => 'nullable|string|max:255',
                'submitRequirements' => 'nullable|string',
                'submitExcerpt' => 'nullable|string|max:500',
                'submitFiles.*' => 'nullable|file|max:51200',
            ]);

            $requirementsArray = array_values(array_filter(array_map('trim', explode("\n", $this->submitRequirements))));

            $opp = Opportunity::create([
                'title' => $this->submitTitle,
                'type' => $this->submitOpportunityType,
                'provider' => $this->submitProvider,
                'link_url' => $this->submitOpportunityLink,
                'expires_at' => $this->submitOpportunityDeadline ?: null,
                'description' => $this->submitExcerpt ?: $this->submitTitle,
                'is_published' => $isPublished,
                'extra' => [
                    'location' => $this->submitLocation ?: 'Remote',
                    'compensation' => $this->submitCompensation ?: 'Unspecified',
                    'requirements' => $requirementsArray,
                ],
            ]);

            $post = HubPost::create([
                'title' => $this->submitTitle,
                'type' => 'opportunity',
                'category' => $this->submitCategory ?: 'Career',
                'excerpt' => $this->submitExcerpt ?: "Hosted by {$this->submitProvider}",
                'content' => $this->submitRequirements,
                'opportunity_link' => $this->submitOpportunityLink,
                'opportunity_deadline' => $this->submitOpportunityDeadline ?: null,
                'is_published' => $isPublished,
                'author_id' => $user->id,
                'extra' => [
                    'provider' => $this->submitProvider,
                    'opportunity_type' => $this->submitOpportunityType,
                    'location' => $this->submitLocation ?: 'Remote',
                    'compensation' => $this->submitCompensation ?: 'Unspecified',
                    'requirements' => $requirementsArray,
                    'opportunity_id' => $opp->id,
                ],
            ]);

            $this->storeAttachments($post);
            $this->storeAttachments($opp);

        } else {
            $this->validate([
                'submitTitle' => 'required|string|max:255',
                'submitType' => 'required|string|in:video,tip_trick,blog',
                'submitCategory' => 'required|string|max:255',
                'submitExcerpt' => 'nullable|string|max:500',
                'submitContent' => 'nullable|string',
                'submitCodeSnippet' => 'nullable|string',
                'submitProTip' => 'nullable|string|max:500',
                'submitYoutubeUrl' => 'nullable|url|max:255',
                'submitFiles.*' => 'nullable|file|max:51200',
            ]);

            $post = HubPost::create([
                'title' => $this->submitTitle,
                'type' => $this->submitType,
                'category' => $this->submitCategory,
                'excerpt' => $this->submitExcerpt,
                'content' => $this->submitContent,
                'code_snippet' => $this->submitCodeSnippet ?: null,
                'pro_tip' => $this->submitProTip ?: null,
                'youtube_url' => $this->submitYoutubeUrl ?: null,
                'is_published' => $isPublished,
                'author_id' => $user->id,
            ]);

            $this->storeAttachments($post);
        }

        if ($isPublished) {
            $this->submitNoticeMessage = 'Your resource has been published live!';
        } else {
            $this->submitNoticeMessage = 'Thank you! Your submission has been received and will be reviewed by an Admin before going public.';
        }

        $this->closeSubmitModal();
    }

    protected function storeAttachments($model): void
    {
        if (empty($this->submitFiles)) {
            return;
        }

        foreach ($this->submitFiles as $file) {
            $path = $file->store('hub-media', 'public');

            Media::create([
                'mediable_type' => get_class($model),
                'mediable_id' => $model->id,
                'disk' => 'public',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                'size_bytes' => $file->getSize() ?? 0,
                'status' => 'ready',
            ]);
        }
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

    public function openVideoModal(int $postId): void
    {
        $post = HubPost::published()->find($postId);
        if ($post && $post->type === 'video') {
            $this->activeVideoId = $post->id;
        }
    }

    public function closeVideoModal(): void
    {
        $this->activeVideoId = null;
    }

    public function render(): View
    {
        $posts = HubPost::query()
            ->with(['author', 'media'])
            ->published()
            ->search($this->search)
            ->type($this->type)
            ->category($this->category)
            ->orderByRaw("CASE WHEN type = 'video' THEN 1 WHEN type = 'tip_trick' THEN 2 WHEN type = 'blog' THEN 3 ELSE 4 END")
            ->latest()
            ->paginate(9);

        $categories = HubPost::categoryOptions();
        $activeVideo = $this->activeVideoId ? HubPost::with('media')->find($this->activeVideoId) : null;

        return view('livewire.public.hub-index', [
            'posts' => $posts,
            'categories' => $categories,
            'activeVideo' => $activeVideo,
        ])->layout('layouts.public', [
            'title' => 'Knowledge & Opportunities Hub',
        ]);
    }
}
