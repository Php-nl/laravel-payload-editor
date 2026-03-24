<?php

namespace PhpNl\LaravelPayloadEditor\Repositories;

use PhpNl\LaravelPayloadEditor\Contracts\FailedJobRepository;
use RuntimeException;

class HorizonFailedJobRepository implements FailedJobRepository
{
    public function find(string $uuid): ?object
    {
        throw new RuntimeException('Horizon Redis implementation is planned for v2.0. Please use the database driver.');
    }

    public function paginate(int $perPage = 15): mixed
    {
        throw new RuntimeException('Horizon Redis implementation is planned for v2.0. Please use the database driver.');
    }

    public function updatePayload(string $uuid, string $payload): bool
    {
        throw new RuntimeException('Horizon Redis implementation is planned for v2.0. Please use the database driver.');
    }

    public function retry(string $uuid): bool
    {
        throw new RuntimeException('Horizon Redis implementation is planned for v2.0. Please use the database driver.');
    }
}
