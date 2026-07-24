<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Temporary file uploads
    |--------------------------------------------------------------------------
    | Partial config (merged over Livewire's package defaults): raise the
    | temporary upload cap so video files up to 30 MB survive Livewire's
    | validation. Kept deliberately below php.ini upload_max_filesize /
    | post_max_size (both 40M on this machine).
    */
    'temporary_file_upload' => [
        'rules' => 'required|file|max:30720',
    ],
];
