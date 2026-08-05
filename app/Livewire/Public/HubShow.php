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

        $this->post = $post->load(['author:id,name,profile_photo_path']);
    }

    public function render(): View
    {
        $relatedPosts = HubPost::query()
            ->published()
            ->whereKeyNot($this->post->id)
            ->latest()
            ->limit(3)
            ->get();

        return view('livewire.public.hub-show', [
            'post' => $this->post,
            'relatedPosts' => $relatedPosts,
        ])->layout('layouts.public', [
            'title' => $this->post->title,
            'description' => $this->post->excerpt ?? Str::limit(strip_tags($this->post->content ?? ''), 150),
        ]);
    }
}
