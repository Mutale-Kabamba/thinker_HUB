<?php
use Illuminate\Support\Facades\Schema;
Schema::table('users', function($t){ $t->string('track')->nullable()->default(null)->change(); });
App\Models\User::where('role','instructor')->update(['track' => null]);
echo 'Fixed';
