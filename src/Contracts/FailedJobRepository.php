<?php

namespace PhpNl\LaravelPayloadEditor\Contracts;

interface FailedJobRepository
{
    /**
     * Retrieve a failed job by its UUID.
     */
    public function find(string $uuid): ?object;

    /**
     * Retrieve a paginated list of failed jobs.
     */
    public function paginate(int $perPage = 15): mixed;

    /**
     * Update the payload of a failed job.
     */
    public function updatePayload(string $uuid, string $payload): bool;

    /**
     * Retry a failed job.
     */
    public function retry(string $uuid): bool;
}
