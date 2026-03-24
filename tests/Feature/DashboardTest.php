<?php

namespace PhpNl\LaravelPayloadEditor\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use PhpNl\LaravelPayloadEditor\Livewire\LaravelPayloadEditorDashboard;
use PhpNl\LaravelPayloadEditor\Tests\TestCase;

class DashboardTest extends TestCase
{
    public function test_it_can_render_dashboard()
    {
        $this->withoutExceptionHandling();

        $response = $this->get('/laravel-payload-editor');

        $response->assertStatus(200);
        $response->assertSeeLivewire(LaravelPayloadEditorDashboard::class);
    }

    public function test_it_displays_failed_jobs()
    {
        DB::table('failed_jobs')->insert([
            'uuid' => 'test-uuid-1234',
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'App\\Jobs\\TestJob']),
            'exception' => 'Test Exception',
            'failed_at' => now(),
        ]);

        Livewire::test(LaravelPayloadEditorDashboard::class)
            ->assertSee('test-uuid-1234')
            ->assertSee('App\Jobs\TestJob');
    }
    public function test_it_can_inspect_and_retry_job()
    {
        DB::table('failed_jobs')->insert([
            'uuid' => 'test-uuid-inspect',
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode([
                'displayName' => 'App\\Jobs\\TestJob',
                'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
                'data' => [
                    'commandName' => 'App\\Jobs\\TestJob',
                    'command' => serialize(new \stdClass()),
                ],
            ]),
            'exception' => 'Test Exception',
            'failed_at' => now(),
        ]);

        Livewire::test(LaravelPayloadEditorDashboard::class)
            ->call('inspect', 'test-uuid-inspect', app(\PhpNl\LaravelPayloadEditor\Contracts\FailedJobRepository::class), app(\PhpNl\LaravelPayloadEditor\Engine\JobPayloadManager::class))
            ->assertSet('errorMessage', null)
            ->assertSet('inspectingJobUuid', 'test-uuid-inspect')
            ->call('saveAndRetry', app(\PhpNl\LaravelPayloadEditor\Contracts\FailedJobRepository::class), app(\PhpNl\LaravelPayloadEditor\Engine\JobPayloadManager::class))
            ->assertSet('errorMessage', null)
            ->assertSet('inspectingJobUuid', null);
    }
}
