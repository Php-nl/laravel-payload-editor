<?php

namespace PhpNl\LaravelPayloadEditor\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use PhpNl\LaravelPayloadEditor\LaravelPayloadEditorServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('queue.failed.database', config('database.default'));
        $this->setUpDatabase();

        Gate::define('viewLaravelPayloadEditor', function ($user = null) {
            return true;
        });
    }

    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
            LaravelPayloadEditorServiceProvider::class,
        ];
    }

    protected function setUpDatabase()
    {
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->longText('payload');
            $table->tinyInteger('attempts')->unsigned();
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });
    }
}
