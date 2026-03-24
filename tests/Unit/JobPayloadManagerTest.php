<?php

namespace PhpNl\LaravelPayloadEditor\Tests\Unit;

use PhpNl\LaravelPayloadEditor\Engine\JobPayloadManager;
use PhpNl\LaravelPayloadEditor\Tests\TestCase;

class DummyJob
{
    public int $orderId;

    public string $email;

    public bool $isPaid;

    public function __construct(int $orderId, string $email, bool $isPaid)
    {
        $this->orderId = $orderId;
        $this->email = $email;
        $this->isPaid = $isPaid;
    }
}

class JobPayloadManagerTest extends TestCase
{
    public function test_it_unserializes_command()
    {
        $manager = new JobPayloadManager;
        $job = new DummyJob(10, 'test@test.com', true);

        $json = json_encode([
            'data' => [
                'command' => serialize($job),
            ],
        ]);

        $command = $manager->unserializeCommand($json);
        $this->assertInstanceOf(DummyJob::class, $command);
        $this->assertEquals(10, $command->orderId);
    }

    public function test_it_rebuilds_schema()
    {
        $manager = new JobPayloadManager;
        $job = new DummyJob(10, 'test@test.com', true);

        $schema = $manager->getEditableSchema($job);

        $this->assertEquals('int', $schema['orderId']['type']);
        $this->assertEquals(10, $schema['orderId']['value']);
        $this->assertTrue($schema['orderId']['editable']);

        $this->assertEquals('bool', $schema['isPaid']['type']);
        $this->assertTrue($schema['isPaid']['value']);
    }

    public function test_it_casts_and_modifies_payload()
    {
        $manager = new JobPayloadManager;
        $job = new DummyJob(10, 'test@test.com', true);

        // Form input is often strings
        $updates = [
            'orderId' => '25',
            'email' => 'changed@test.com',
            'isPaid' => '0',
        ];

        $newSerialized = $manager->modifyAndSerialize($job, $updates);
        $newJob = unserialize($newSerialized);

        $this->assertSame(25, $newJob->orderId);
        $this->assertSame('changed@test.com', $newJob->email);
        $this->assertSame(false, $newJob->isPaid);
    }
}
