<?php

use Illuminate\Support\Facades\Route;
use PhpNl\LaravelPayloadEditor\Http\Middleware\Authenticate;
use PhpNl\LaravelPayloadEditor\Livewire\LaravelPayloadEditorDashboard;

Route::group([
    'domain' => config('laravel-payload-editor.domain', null),
    'prefix' => config('laravel-payload-editor.path', 'laravel-payload-editor'),
    'middleware' => array_merge(config('laravel-payload-editor.middleware', ['web']), [Authenticate::class]),
], function () {
    Route::get('/', LaravelPayloadEditorDashboard::class)->name('laravel-payload-editor.index');
});
