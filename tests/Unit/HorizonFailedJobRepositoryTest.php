<?php

namespace PhpNl\LaravelPayloadEditor\Tests\Unit;

use PhpNl\LaravelPayloadEditor\Repositories\HorizonFailedJobRepository;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class HorizonFailedJobRepositoryTest extends TestCase
{
    private HorizonFailedJobRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = new HorizonFailedJobRepository();
    }

    public function test_find_throws_exception()
    {
        $this->expectException(RuntimeException::class);
        $this->repository->find('uuid');
    }

    public function test_paginate_throws_exception()
    {
        $this->expectException(RuntimeException::class);
        $this->repository->paginate();
    }

    public function test_update_payload_throws_exception()
    {
        $this->expectException(RuntimeException::class);
        $this->repository->updatePayload('uuid', 'payload');
    }

    public function test_retry_throws_exception()
    {
        $this->expectException(RuntimeException::class);
        $this->repository->retry('uuid');
    }
}
