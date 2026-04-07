<?php

/*
 * This config file overrides only the keys we need to customise.
 * Anything not defined here falls back to the package defaults via
 * mergeConfigFrom() inside Livewire's service provider.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Livewire Temporary File Uploads
    |--------------------------------------------------------------------------
    |
    | Default Livewire allows max 12 MB per upload. The Messenger feature
    | needs up to 30 MB for non-image attachments, so we raise the rule
    | here. Per-component validation enforces stricter limits where needed
    | (e.g. 10 MB for images).
    |
    */

    'temporary_file_upload' => [
        'disk' => null,
        'rules' => ['required', 'file', 'max:30720'], // 30 MB
        'directory' => null,
        'middleware' => null,
        'preview_mimes' => [
            'png', 'gif', 'bmp', 'svg', 'wav', 'mp4',
            'mov', 'avi', 'wmv', 'mp3', 'm4a',
            'jpg', 'jpeg', 'mpga', 'webp', 'wma',
        ],
        'max_upload_time' => 5,
    ],

];
