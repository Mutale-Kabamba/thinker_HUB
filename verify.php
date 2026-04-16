<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
echo json_encode(App\Models\User::where('role','instructor')->get(['id','name','email','role','track','is_active','email_verified_at']), JSON_PRETTY_PRINT);
