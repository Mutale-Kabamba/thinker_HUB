<?php

namespace App\Ai\Tools\Public;

use App\Models\Opportunity;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchOpportunitiesTool implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search active career opportunities, tech jobs, internships, scholarships, and events posted on think.er HUB by keyword or opportunity type.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'keyword' => $schema->string()->nullable(),
            'type' => $schema->string()->nullable(),
        ];
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $keyword = trim((string) ($request['keyword'] ?? ''));
        $type = trim((string) ($request['type'] ?? ''));

        $query = Opportunity::query()->active();

        if ($keyword !== '') {
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
                    ->orWhere('provider', 'like', "%{$keyword}%");
            });
        }

        if ($type !== '') {
            $query->where('type', 'like', "%{$type}%");
        }

        $items = $query->latest()->limit(8)->get();

        if ($items->isEmpty()) {
            return 'No currently active opportunities matching your query were found. Encourage the user to check back regularly as new partner internships and scholarships are posted frequently.';
        }

        $results = [];

        foreach ($items as $item) {
            $results[] = [
                'title' => $item->title,
                'type' => $item->type,
                'provider' => $item->provider ?? 'think.er HUB Partner',
                'description' => $item->description ? substr(strip_tags($item->description), 0, 180) . '...' : null,
                'promo_code' => $item->promo_code,
                'link' => $item->link_url ?? '/learn/opportunities',
                'expires_at' => $item->expires_at ? $item->expires_at->format('M d, Y') : 'Ongoing',
            ];
        }

        return json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}

