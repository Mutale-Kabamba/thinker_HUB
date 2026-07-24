<?php

namespace App\Filament\Resources\ResourceVideos\Pages;

use App\Filament\Resources\Pages\BaseCreateRecord;
use App\Filament\Resources\ResourceVideos\Concerns\HandlesVideoUpload;
use App\Filament\Resources\ResourceVideos\ResourceVideoResource;

class CreateResourceVideo extends BaseCreateRecord
{
    use HandlesVideoUpload;

    protected static string $resource = ResourceVideoResource::class;
}
