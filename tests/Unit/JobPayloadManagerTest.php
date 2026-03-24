<?php

namespace PhpNl\LaravelPayloadEditor\Tests\Unit;

use Illuminate\Contracts\Database\ModelIdentifier;
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

enum TestUnitEnum
{
    case Draft;
    case Published;
}

enum TestBackedEnum: string
{
    case Pending = 'pending';
    case Done = 'done';
}

class AdvancedDummyJob
{
    public TestUnitEnum $unit;

    public TestBackedEnum $backed;

    public ModelIdentifier $identifier;

    public ModelIdentifier $arrayIdentifier;

    public function __construct()
    {
        $this->unit = TestUnitEnum::Draft;
        $this->backed = TestBackedEnum::Pending;

        $this->identifier = new ModelIdentifier('App\\Models\\User', 1, [], null);
        $this->arrayIdentifier = new ModelIdentifier('App\\Models\\User', [1, 2, 3], [], null);
    }
}

class JobPayloadManagerAdvancedTest extends TestCase
{
    public function test_it_handles_enums_and_arrays()
    {
        $manager = new JobPayloadManager;
        $job = new AdvancedDummyJob;

        $schema = $manager->getEditableSchema($job);

        // Assert Unit Enum
        $this->assertEquals(TestUnitEnum::class, $schema['unit']['type']);
        $this->assertEquals('Draft', $schema['unit']['value']);
        $this->assertTrue($schema['unit']['editable']);

        // Assert Backed Enum
        $this->assertEquals(TestBackedEnum::class, $schema['backed']['type']);
        $this->assertEquals('pending', $schema['backed']['value']);
        $this->assertTrue($schema['backed']['editable']);

        // Assert Scalar Identifier
        $this->assertEquals('ModelIdentifier', $schema['identifier']['type']);
        $this->assertEquals(1, $schema['identifier']['value']);
        $this->assertTrue($schema['identifier']['editable']);

        // Assert Array Identifier
        $this->assertEquals('ModelIdentifier (Array)', $schema['arrayIdentifier']['type']);
        $this->assertFalse($schema['arrayIdentifier']['editable']);

        // Test Modification
        $updates = [
            'unit' => 'Published',
            'backed' => 'done',
            'identifier' => '5',
            'arrayIdentifier' => 'bypassed', // Should not change
        ];

        $newSerialized = $manager->modifyAndSerialize($job, $updates);
        $newJob = unserialize($newSerialized);

        $this->assertEquals(TestUnitEnum::Published, $newJob->unit);
        $this->assertEquals(TestBackedEnum::Done, $newJob->backed);
        $this->assertEquals(5, $newJob->identifier->id);
        $this->assertEquals([1, 2, 3], $newJob->arrayIdentifier->id);
    }
}
