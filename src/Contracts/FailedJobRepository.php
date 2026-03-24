<?php

namespace PhpNl\LaravelPayloadEditor\Contracts;

interface FailedJobRepository
{
    /**
     * Retrieve a failed job by its UUID.
     */
    public function find(string $uuid): ?object;

    /**
     * Retrieve a paginated list of failed jobs, optionally filtered by a search query.
     */
    public function paginate(int $perPage = 15, string $search = ''): mixed;

    /**
     * Update the payload of a failed job.
     */
    public function updatePayload(string $uuid, string $payload): bool;

    /**
     * Retry a failed job.
     */
    public function retry(string $uuid): bool;

    /**
     * Delete a failed job by its UUID.
     */
    public function delete(string $uuid): bool;

    /**
     * Delete all failed jobs from the repository.
     */
    public function flush(): bool;
}
