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
    public string $customSpecialty = '';

    // Lookup & Auto-fill from Existing Account
    public string $lookupEmail = '';
    public ?int $existingUserId = null;
    public ?string $existingUserRole = null;
    public ?string $lookupMessage = null;
    public string $lookupStatus = ''; // 'success', 'error', 'info'

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

    public function getSpecialtyOptionsProperty(): array
    {
        return match ($this->regRole) {
            'blogger' => [
                'Web & Full Stack Development',
                'Artificial Intelligence & Machine Learning',
                'Cloud Computing & DevOps',
                'Cybersecurity & Information Security',
                'Mobile App Development (iOS / Android / Flutter)',
                'Data Science & Analytics',
                'UI/UX & Digital Product Design',
                'Blockchain & Web3 Technologies',
                'Tech Career, Mentorship & Freelancing',
                'Software Architecture & Engineering Practices',
                'Internet of Things (IoT) & Embedded Systems',
                'Tech Entrepreneurship & Product Management',
                'Other / Custom Specialty',
            ],
            'researcher' => [
                'Backend Architecture & API Engineering (Laravel, Python, Node.js, Go)',
                'Frontend Frameworks & Web Performance (Vue, React, Alpine.js, Tailwind)',
                'Database Optimization & SQL Query Tuning (MySQL, PostgreSQL, Redis)',
                'Machine Learning, Deep Learning & LLM Research',
                'System Design, Scalability & Microservices',
                'DevOps, CI/CD Pipelines & Containerization (Docker, Kubernetes)',
                'Applied Cybersecurity, Ethical Hacking & Security Audits',
                'Algorithms, Data Structures & Performance Optimization',
                'Clean Code, Refactoring & Software Design Patterns',
                'Test Automation, QA & Test-Driven Development (TDD)',
                'Computer Vision & Natural Language Processing (NLP)',
                'Cloud Architecture & Infrastructure as Code (AWS, GCP, Azure, Terraform)',
                'Other / Custom Specialty',
            ],
            'employer' => [
                'Full Stack & Web Engineering Hiring',
                'Data Analytics & Machine Learning Roles',
                'Cloud Infrastructure & DevOps Opportunities',
                'UI/UX & Product Design Recruiting',
                'Cybersecurity & Network Systems Positions',
                'Mobile Application Engineering Roles',
                'Tech Internships & Graduate Trainee Programs',
                'IT Support, Systems & Database Administration',
                'QA Testing & Automation Engineering',
                'Technical Project Management & Scrum Leadership',
                'Remote Freelance & Contract Engineering',
                'Corporate Partnerships & University Fellowships',
                'Other / Custom Specialty',
            ],
            default => [
                'Software Development',
                'Data & AI',
                'Cloud & DevOps',
                'Cybersecurity',
                'Product & Design',
                'Other / Custom Specialty',
            ],
        };
    }

    public function updatedRegRole(): void
    {
        $options = $this->specialtyOptions;
        if (! in_array($this->regSpecialty, $options, true) && $this->regSpecialty !== 'Other / Custom Specialty') {
            $this->regSpecialty = $options[0] ?? '';
        }
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
        $this->regFacebookUrl = '';
        $this->regPortfolioUrl = '';
        $this->regGithubUrl = '';
        $this->regInstagramUrl = '';
        $this->regCompany = '';
        $this->regSpecialty = $this->specialtyOptions[0] ?? '';
        $this->customSpecialty = '';

        $this->lookupEmail = '';
        $this->existingUserId = null;
        $this->existingUserRole = null;
        $this->lookupMessage = null;
        $this->lookupStatus = '';

        if (auth()->check()) {
            $user = auth()->user();
            $this->lookupEmail = (string) $user->email;
            $this->checkAndPullDetails($user->email);
        }

        $this->showRegisterModal = true;
    }

    public function checkAndPullDetails(?string $emailToLookup = null): void
    {
        $email = trim($emailToLookup ?? $this->lookupEmail);

        if ($email === '') {
            $this->lookupStatus = 'error';
            $this->lookupMessage = 'Please enter your registered student or instructor email address to check.';
            return;
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $this->lookupStatus = 'error';
            $this->lookupMessage = "No existing account found for \"{$email}\". You can proceed with standard contributor registration below.";
            $this->existingUserId = null;
            $this->existingUserRole = null;
            return;
        }

        // Pull application details if available to get maximum social and contact data
        $application = $user->instructorApplication ?? \App\Models\InstructorApplication::query()->where('email', $user->email)->latest()->first();

        // Auto-fill all available profile attributes from existing account & application
        $this->regName = $user->name ?? ($application?->name ?? '');
        $this->regEmail = $user->email ?? '';
        $this->regWhatsapp = $user->whatsapp ?: ($application?->whatsapp ?: ($application?->phone ?: ''));
        $this->regBio = $user->bio ?: ($application?->bio ?: '');
        $this->regLinkedinUrl = $user->linkedin_url ?: ($application?->linkedin_url ?: '');
        $this->regFacebookUrl = $user->facebook_url ?: ($application?->facebook_url ?: '');
        $this->regPortfolioUrl = $user->portfolio_url ?: ($application?->portfolio_url ?: '');
        $this->regGithubUrl = $user->github_url ?: '';
        $this->regInstagramUrl = $user->instagram_url ?: '';
        $this->regCompany = $user->company ?: ($user->occupation ?: ($application?->occupation ?? ''));
        
        $importedSpecialty = trim($user->specialty ?: ($user->proficiency ?: ($application?->proficiency ?: ($user->track ?: ''))));
        $options = $this->specialtyOptions;
        if (in_array($importedSpecialty, $options, true)) {
            $this->regSpecialty = $importedSpecialty;
            $this->customSpecialty = '';
        } elseif ($importedSpecialty !== '') {
            $this->regSpecialty = 'Other / Custom Specialty';
            $this->customSpecialty = $importedSpecialty;
        } else {
            $this->regSpecialty = $options[0] ?? '';
            $this->customSpecialty = '';
        }

        $this->existingUserId = $user->id;
        $this->existingUserRole = ucfirst($user->role);
        $this->lookupStatus = 'success';
        $this->lookupMessage = "✓ Verified existing {$this->existingUserRole} profile for {$user->name}! Profile details, social links, and contacts have been loaded. Your current account password will be used.";
    }

    public function closeRegisterModal(): void
    {
        $this->showRegisterModal = false;
        $this->lookupMessage = null;
        $this->lookupStatus = '';
        $this->existingUserId = null;
        $this->existingUserRole = null;
    }

    public function registerContributor(): void
    {
        $validationRules = [
            'regName' => 'required|string|max:255',
            'regEmail' => $this->existingUserId
                ? 'required|string|email|max:255|unique:users,email,'.$this->existingUserId
                : 'required|string|email|max:255|unique:users,email',
            'regRole' => 'required|string|in:blogger,researcher,employer',
            'regBio' => 'nullable|string|max:1000',
            'regWhatsapp' => 'nullable|string|max:50',
            'regLinkedinUrl' => 'nullable|url|max:255',
            'regFacebookUrl' => 'nullable|url|max:255',
            'regPortfolioUrl' => 'nullable|url|max:255',
            'regGithubUrl' => 'nullable|url|max:255',
            'regInstagramUrl' => 'nullable|url|max:255',
            'regCompany' => 'nullable|string|max:255',
            'regSpecialty' => 'required|string|max:255',
            'customSpecialty' => 'nullable|string|max:255',
        ];

        if (! $this->existingUserId) {
            $validationRules['regPassword'] = 'required|string|min:8|same:regPasswordConfirmation';
        }

        $this->validate($validationRules);

        $roleTitle = match ($this->regRole) {
            'blogger' => 'Blogger (Short Blogs)',
            'researcher' => 'Researcher (Tips & Tricks)',
            'employer' => 'Employer (Opportunities)',
            default => ucfirst($this->regRole),
        };

        $resolvedSpecialty = $this->regSpecialty === 'Other / Custom Specialty'
            ? ($this->customSpecialty ?: 'General Technical Specialty')
            : $this->regSpecialty;

        if ($this->existingUserId && ($existingUser = User::find($this->existingUserId))) {
            $updateData = [
                'name' => $this->regName,
                'email' => $this->regEmail,
                'bio' => $this->regBio ?: $existingUser->bio,
                'whatsapp' => $this->regWhatsapp ?: $existingUser->whatsapp,
                'linkedin_url' => $this->regLinkedinUrl ?: $existingUser->linkedin_url,
                'facebook_url' => $this->regFacebookUrl ?: $existingUser->facebook_url,
                'portfolio_url' => $this->regPortfolioUrl ?: $existingUser->portfolio_url,
                'github_url' => $this->regGithubUrl ?: $existingUser->github_url,
                'instagram_url' => $this->regInstagramUrl ?: $existingUser->instagram_url,
                'company' => $this->regCompany ?: $existingUser->company,
                'specialty' => $resolvedSpecialty ?: $existingUser->specialty,
            ];

            if ($this->regPassword) {
                $updateData['password'] = Hash::make($this->regPassword);
            }

            // If user is a student, request/switch to contributor role pending approval
            if ($existingUser->role === 'student') {
                $updateData['role'] = $this->regRole;
                $updateData['is_active'] = false; // Pending Admin approval for contributor publishing
            } elseif (in_array($existingUser->role, ['blogger', 'researcher', 'employer'])) {
                $updateData['role'] = $this->regRole;
            }

            $existingUser->update($updateData);
        } else {
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
                'specialty' => $resolvedSpecialty ?: null,
            ]);
        }

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
                'submitFiles' => 'nullable|array|max:5',
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

            try {
                app(\App\Services\GamificationService::class)->awardOpportunitySubmission($user, $opp->id, $this->submitTitle);
            } catch (\Throwable $e) {
                report($e);
            }

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
                'submitFiles' => 'nullable|array|max:5',
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

            if ($isPublished) {
                try {
                    app(\App\Services\GamificationService::class)->awardHubPost($user, $post);
                } catch (\Throwable $e) {
                    report($e);
                }
            }
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
