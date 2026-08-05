<?php

namespace App\Filament\Resources\HubPosts\Schemas;

use App\Models\HubPost;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class HubPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Post Metadata')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, callable $set) {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(HubPost::class, 'slug', ignoreRecord: true)
                            ->helperText('Unique URL-friendly string.'),

                        Select::make('type')
                            ->required()
                            ->options(HubPost::TYPES)
                            ->default('blog')
                            ->live(),

                        Select::make('category')
                            ->required()
                            ->options(HubPost::categoryOptions())
                            ->searchable()
                            ->default('General')
                            ->createOptionForm([
                                TextInput::make('category')
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Toggle::make('is_published')
                            ->label('Published (visible on public Hub)')
                            ->default(fn () => auth()->user()?->isAdmin() ?? false)
                            ->disabled(fn () => ! (auth()->user()?->isAdmin() ?? false))
                            ->helperText(fn () => ! (auth()->user()?->isAdmin() ?? false)
                                ? 'Your submission will be reviewed and approved by an Admin before going public.'
                                : 'Publish immediately to the public Knowledge Hub.'),
                    ])
                    ->columns(2),

                Section::make('Content & Excerpt')
                    ->schema([
                        Textarea::make('excerpt')
                            ->label('Excerpt / Brief Summary')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Short overview displayed on hub cards.'),

                        RichEditor::make('content')
                            ->label('Full Content (Rich Text)')
                            ->columnSpanFull()
                            ->visible(fn (callable $get): bool => in_array($get('type'), ['blog', 'tip_trick'], true)),
                    ]),

                Section::make('Tip & Trick Details')
                    ->schema([
                        Textarea::make('code_snippet')
                            ->label('Code Snippet')
                            ->rows(4)
                            ->columnSpanFull()
                            ->helperText('Formatted code snippet displayed on the tip card.'),

                        Textarea::make('pro_tip')
                            ->label('Pro Tip / Key Takeaway')
                            ->rows(2)
                            ->columnSpanFull()
                            ->helperText('Highlighted callout box for quick tips.'),
                    ])
                    ->visible(fn (callable $get): bool => $get('type') === 'tip_trick'),

                Section::make('Video Details')
                    ->schema([
                        TextInput::make('youtube_url')
                            ->label('YouTube URL')
                            ->url()
                            ->placeholder('https://www.youtube.com/watch?v=...')
                            ->maxLength(255)
                            ->helperText('Video ID will be auto-extracted upon saving.')
                            ->required(fn (callable $get): bool => $get('type') === 'video'),
                    ])
                    ->visible(fn (callable $get): bool => $get('type') === 'video'),

                Section::make('Opportunity Details')
                    ->schema([
                        TextInput::make('opportunity_link')
                            ->label('Application / External Link')
                            ->url()
                            ->placeholder('https://example.com/apply')
                            ->maxLength(255),

                        DatePicker::make('opportunity_deadline')
                            ->label('Application / Event Deadline')
                            ->native(false)
                            ->helperText('Leave empty for no deadline.'),
                    ])
                    ->columns(2)
                    ->visible(fn (callable $get): bool => $get('type') === 'opportunity'),
            ]);
    }
}
