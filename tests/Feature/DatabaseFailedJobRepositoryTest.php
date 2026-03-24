<?php

namespace PhpNl\LaravelPayloadEditor\Tests\Feature;

use Illuminate\Support\Facades\DB;
use PhpNl\LaravelPayloadEditor\Repositories\DatabaseFailedJobRepository;
use PhpNl\LaravelPayloadEditor\Tests\TestCase;

class DatabaseFailedJobRepositoryTest extends TestCase
{
    private DatabaseFailedJobRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new DatabaseFailedJobRepository;
    }

    public function test_it_can_find_a_failed_job_by_uuid()
    {
        DB::table('failed_jobs')->insert([
            'uuid' => 'test-uuid-1',
            'connection' => 'database',
            'queue' => 'default',
            'payload' => '{"foo":"bar"}',
            'exception' => 'Test',
            'failed_at' => now(),
        ]);

        $job = $this->repository->find('test-uuid-1');

        $this->assertNotNull($job);
        $this->assertEquals('test-uuid-1', $job->uuid);
    }

    public function test_it_returns_null_when_job_not_found()
    {
        $job = $this->repository->find('non-existent-uuid');

        $this->assertNull($job);
    }

    public function test_it_can_paginate_failed_jobs()
    {
        for ($i = 1; $i <= 20; $i++) {
            DB::table('failed_jobs')->insert([
                'uuid' => "test-uuid-{$i}",
                'connection' => 'database',
                'queue' => 'default',
                'payload' => '{"foo":"bar"}',
                'exception' => 'Test',
                'failed_at' => now(),
            ]);
        }

        $paginator = $this->repository->paginate(15);

        $this->assertEquals(15, $paginator->count());
        $this->assertEquals(20, $paginator->total());
    }

    public function test_it_can_update_a_payload()
    {
        DB::table('failed_jobs')->insert([
            'uuid' => 'update-uuid',
            'connection' => 'database',
            'queue' => 'default',
            'payload' => '{"foo":"bar"}',
            'exception' => 'Test',
            'failed_at' => now(),
        ]);

        $success = $this->repository->updatePayload('update-uuid', '{"foo":"baz"}');

        $this->assertTrue($success);
        $this->assertEquals('{"foo":"baz"}', DB::table('failed_jobs')->where('uuid', 'update-uuid')->value('payload'));
    }

    public function test_it_can_retry_a_failed_job()
    {
        DB::table('failed_jobs')->insert([
            'uuid' => 'retry-uuid',
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode([
                'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
                'data' => [
                    'commandName' => 'stdClass',
                    'command' => serialize(new \stdClass),
                ],
            ]),
            'exception' => 'Test',
            'failed_at' => now(),
        ]);

        $success = $this->repository->retry('retry-uuid');

        $this->assertTrue($success);
        $this->assertNull(DB::table('failed_jobs')->where('uuid', 'retry-uuid')->first());
    }
}
