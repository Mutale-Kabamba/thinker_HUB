<?php

namespace App\Filament\Resources\HubPosts\Pages;

use App\Filament\Resources\HubPosts\HubPostResource;
use App\Filament\Resources\Pages\BaseCreateRecord;

class CreateHubPost extends BaseCreateRecord
{
    protected static string $resource = HubPostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['author_id'] = auth()->id();

        return $data;
    }
}
