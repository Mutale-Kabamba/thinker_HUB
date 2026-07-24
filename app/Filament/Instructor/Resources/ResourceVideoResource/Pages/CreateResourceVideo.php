<?php

namespace App\Filament\Instructor\Resources\ResourceVideoResource\Pages;

use App\Filament\Instructor\Resources\ResourceVideoResource\ResourceVideoResource;
use App\Filament\Resources\Pages\BaseCreateRecord;
use App\Filament\Resources\ResourceVideos\Concerns\HandlesVideoUpload;

class CreateResourceVideo extends BaseCreateRecord
{
    use HandlesVideoUpload;

    protected static string $resource = ResourceVideoResource::class;
}
