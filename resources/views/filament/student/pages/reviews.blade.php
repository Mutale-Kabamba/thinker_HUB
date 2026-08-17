<x-filament-panels::page>
    <div class="space-y-8">
        @livewire(\App\Livewire\Reviews\CreateReviewPage::class, [
            'targetType' => request('type', 'platform'),
            'targetId' => request('id'),
        ])
    </div>
</x-filament-panels::page>
