<?php

namespace App\Filament\Contributor\Resources\HubPostResource\Pages;

use App\Filament\Contributor\Resources\HubPostResource;
use App\Filament\Resources\Pages\BaseCreateRecord;

class CreateHubPost extends BaseCreateRecord
{
    protected static string $resource = HubPostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        $data['author_id'] = $user?->id;

        // Automatically default type if missing based on role
        if (empty($data['type'])) {
            if ($user?->isBlogger()) {
                $data['type'] = 'blog';
            } elseif ($user?->isResearcher()) {
                $data['type'] = 'tip_trick';
            } elseif ($user?->isEmployer()) {
                $data['type'] = 'opportunity';
            }
        }

        if (! ($user?->isAdmin() ?? false)) {
            $data['is_published'] = false;
        }

        return $data;
    }
}
