<?php

namespace App\Livewire\Public;

use App\Models\HubPost;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Component;

class HubShow extends Component
{
    public HubPost $post;

    public function mount(HubPost $post): void
    {
        if (! $post->is_published) {
            abort(404);
        }

        $this->post = $post->load(['author:id,name,profile_photo_path', 'media']);
    }

    public function render(): View
    {
        $relatedPosts = HubPost::query()
            ->published()
            ->whereKeyNot($this->post->id)
            ->latest()
            ->limit(3)
            ->get();

        $postUrl = route('hub.show', ['post' => $this->post->slug]);
        $postDescription = $this->post->excerpt ?: Str::limit(trim((string) preg_replace('/\s+/', ' ', strip_tags((string) ($this->post->content ?? '')))), 155);
        $kType = '@' . 'type';

        return view('livewire.public.hub-show', [
            'post' => $this->post,
            'relatedPosts' => $relatedPosts,
        ])->layout('layouts.public', [
            'title' => $this->post->title,
            'description' => $postDescription,
            'url' => $postUrl,
            'type' => 'article',
            'breadcrumbs' => [
                ['name' => 'Knowledge Hub', 'url' => route('hub.index')],
                ['name' => $this->post->title, 'url' => $postUrl],
            ],
            'schema' => [
                $kType => 'BlogPosting',
                'headline' => $this->post->title,
                'description' => $postDescription,
                'url' => $postUrl,
                'datePublished' => ($this->post->published_at ?? $this->post->created_at)?->toIso8601String(),
                'dateModified' => ($this->post->updated_at ?? $this->post->created_at)?->toIso8601String(),
                'author' => [
                    $kType => 'Person',
                    'name' => $this->post->author?->name ?? 'think.er HUB',
                ],
                'publisher' => [
                    $kType => 'EducationalOrganization',
                    'name' => 'think.er HUB',
                    'logo' => [
                        $kType => 'ImageObject',
                        'url' => asset('images/logos/green.png'),
                    ],
                ],
            ],
        ]);
    }
}
