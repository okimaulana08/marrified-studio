<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        'theme_assets' => [
            'driver' => 'local',
            'root' => resource_path('themes'),
            'throw' => false,
        ],

        // User-uploaded media tied to invitations: couple photos, gallery,
        // potentially gift QR codes. Public-visible via storage:link symlink.
        // Files are addressed by invitation_id (not slug) so renaming the
        // invitation slug doesn't orphan the directory.
        //
        // URL is HOST-RELATIVE on purpose — browser uses whatever host the
        // current page is served from. Lets the same image URL work over
        // localhost, LAN IP, and tunneled URLs (cloudflared/ngrok) without
        // re-configuring APP_URL each time.
        'invitation_media' => [
            'driver' => 'local',
            'root' => storage_path('app/public/invitations'),
            'url' => '/storage/invitations',
            'visibility' => 'public',
            'throw' => false,
        ],

        // Admin-curated music library shared across all invitations. Files
        // are addressed by ULID under `tracks/`. Same host-relative URL
        // pattern as invitation_media so it works across localhost, LAN, and
        // tunneled URLs without re-configuring APP_URL.
        'music_assets' => [
            'driver' => 'local',
            'root' => storage_path('app/public/music'),
            'url' => '/storage/music',
            'visibility' => 'public',
            'throw' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
