<?php

namespace App\Filament\Student\Pages;

use Filament\Pages\Page;

class Reviews extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static string|\UnitEnum|null $navigationGroup = 'GROWTH & SOCIAL';

    protected static ?string $navigationLabel = 'Reviews & Ratings';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Reviews & Ratings';

    protected static ?string $slug = 'reviews';

    protected string $view = 'filament.student.pages.reviews';
}
