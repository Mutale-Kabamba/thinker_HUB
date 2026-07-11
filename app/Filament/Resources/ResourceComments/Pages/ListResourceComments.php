<?php

namespace App\Filament\Resources\ResourceComments\Pages;

use App\Filament\Resources\ResourceComments\ResourceCommentResource;
use Filament\Resources\Pages\ListRecords;

class ListResourceComments extends ListRecords
{
    protected static string $resource = ResourceCommentResource::class;
}
