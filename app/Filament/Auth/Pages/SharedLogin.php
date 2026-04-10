<?php

namespace App\Filament\Auth\Pages;

use Filament\Auth\Pages\Login;

class SharedLogin extends Login
{
    public function mount(): void
    {
        redirect()->route('login');
    }
}
