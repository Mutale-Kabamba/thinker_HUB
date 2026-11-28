<?php

namespace App\Ai\Tools\Public;

use App\Models\HubPost;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchArticlesTool implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search published community tech articles, programming tips, tutorials, and career advice published on the Thinker Hub (/hub).';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'keyword' => $schema->string()->nullable(),
            'category' => $schema->string()->nullable(),
        ];
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $keyword = trim((string) ($request['keyword'] ?? ''));
        $category = trim((string) ($request['category'] ?? ''));

        $query = HubPost::query()->where('is_published', true);

        if ($keyword !== '') {
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('excerpt', 'like', "%{$keyword}%")
                    ->orWhere('content', 'like', "%{$keyword}%");
            });
        }

        if ($category !== '') {
            $query->where('category', 'like', "%{$category}%");
        }

        $posts = $query->latest()->limit(6)->get();

        if ($posts->isEmpty()) {
            return 'No published articles matching those criteria were found on the Thinker Hub. Mention that learners can browse all published community articles at /hub or write their own via the Contributor Studio.';
        }

        $results = [];

        foreach ($posts as $post) {
            $results[] = [
                'title' => $post->title,
                'category' => $post->category ?? 'Technology',
                'type' => $post->type ?? 'article',
                'excerpt' => $post->excerpt ?: ($post->content ? substr(strip_tags($post->content), 0, 160) . '...' : null),
                'read_url' => url('/hub/' . ($post->slug ?: $post->id)),
                'published_at' => $post->created_at ? $post->created_at->format('M d, Y') : null,
            ];
        }

        return json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}

