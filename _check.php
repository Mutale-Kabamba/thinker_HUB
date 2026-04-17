<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

App\Models\User::where('role', 'instructor')->update([
    'proficiency' => 'Data Analytics & MS Office',
    'occupation' => 'Software Engineer & Trainer',
    'whatsapp' => '260772640546',
    'linkedin_url' => 'https://linkedin.com/in/mutale-kabamba',
    'facebook_url' => 'https://facebook.com/mutale.kabamba',
]);

$u = App\Models\User::where('role', 'instructor')->first();
echo "Name: {$u->name}\n";
echo "Proficiency: {$u->proficiency}\n";
echo "Occupation: {$u->occupation}\n";
echo "WhatsApp: {$u->whatsapp}\n";
echo "LinkedIn: {$u->linkedin_url}\n";
echo "Facebook: {$u->facebook_url}\n";
echo "DONE\n";
