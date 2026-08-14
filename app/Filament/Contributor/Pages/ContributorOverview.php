<?php

namespace App\Filament\Contributor\Pages;

use App\Models\HubPost;
use Filament\Pages\Page;

class ContributorOverview extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.contributor.pages.overview';

    public static function getNavigationLabel(): string
    {
        $role = auth()->user()?->role;
        return match ($role) {
            'blogger' => 'Blogger Dashboard',
            'researcher' => 'Researcher Dashboard',
            'employer' => 'Employer Dashboard',
            default => 'Contributor Dashboard',
        };
    }

    public function getTitle(): string
    {
        return $this->roleTitle;
    }

    public function getHeading(): string
    {
        return $this->roleTitle;
    }

    public function getSubheading(): ?string
    {
        return $this->roleSubtitle;
    }

    public int $totalPosts = 0;

    public int $publishedPosts = 0;

    public int $pendingPosts = 0;

    public int $totalViews = 0;

    public array $recentPosts = [];

    public string $roleTitle = 'Contributor';

    public string $roleSubtitle = 'Manage your published resources and community contributions.';

    public string $allowedPostType = 'blog';

    public bool $isActive = true;

    public function mount(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $this->isActive = (bool) $user->is_active;

        if ($user->isBlogger()) {
            $this->roleTitle = 'Blogger Workspace';
            $this->roleSubtitle = 'Share your thoughts, short tech blogs, and articles with the Thinker HUB community.';
            $this->allowedPostType = 'blog';
        } elseif ($user->isResearcher()) {
            $this->roleTitle = 'Researcher Workspace';
            $this->roleSubtitle = 'Publish research tips, code walkthroughs, and technical insights.';
            $this->allowedPostType = 'tip_trick';
        } elseif ($user->isEmployer()) {
            $this->roleTitle = 'Employer Workspace';
            $this->roleSubtitle = 'Post career opportunities, job vacancies, and tech scholarships.';
            $this->allowedPostType = 'opportunity';
        }

        $postsQuery = HubPost::query()->where('author_id', $user->id);

        $this->totalPosts = (clone $postsQuery)->count();
        $this->publishedPosts = (clone $postsQuery)->published()->count();
        $this->pendingPosts = (clone $postsQuery)->where('is_published', false)->count();
        $this->totalViews = (int) (clone $postsQuery)->sum('views_count');

        $this->recentPosts = (clone $postsQuery)
            ->latest()
            ->limit(10)
            ->get(['id', 'title', 'slug', 'type', 'category', 'is_published', 'views_count', 'published_at', 'created_at'])
            ->map(fn (HubPost $post) => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'type' => $post->type,
                'category' => $post->category,
                'is_published' => $post->is_published,
                'views_count' => $post->views_count,
                'created_at' => $post->created_at?->format('M d, Y') ?? 'N/A',
            ])
            ->toArray();
    }
}
