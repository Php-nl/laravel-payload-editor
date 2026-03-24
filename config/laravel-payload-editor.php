<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Failed Job Storage Driver
    |--------------------------------------------------------------------------
    |
    | Laravel Payload Editor can read and write to different fail drivers. Standard is
    | 'database', which reads from your `failed_jobs` table. Set to 'horizon'
    | if you exclusively use Horizon's Redis fail driver (experimental).
    |
    */

    'driver' => env('JOB_AUTOPSY_DRIVER', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Laravel Payload Editor Route Domain
    |--------------------------------------------------------------------------
    |
    | This is the subdomain where the Laravel Payload Editor dashboard will be accessible from.
    | If this setting is null, Laravel Payload Editor will reside under the same
    | domain as the application. Otherwise, this value will be used as the subdomain.
    |
    */

    'domain' => env('JOB_AUTOPSY_DOMAIN', null),

    /*
    |--------------------------------------------------------------------------
    | Laravel Payload Editor Route Path
    |--------------------------------------------------------------------------
    |
    | This is the URI path where Laravel Payload Editor will be accessible from.
    | Feel free to change this path to anything you like. Note that the URI
    | will not affect the paths of its internal API that aren't exposed to users.
    |
    */

    'path' => env('JOB_AUTOPSY_PATH', 'laravel-payload-editor'),

    /*
    |--------------------------------------------------------------------------
    | Laravel Payload Editor Route Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will get attached onto each Laravel Payload Editor route, giving you
    | the chance to add your own middleware to this list or change any of
    | the existing middleware. Or, you can simply stick with this list.
    |
    */

    'middleware' => ['web'],

];
