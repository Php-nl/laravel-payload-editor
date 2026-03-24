<?php

namespace PhpNl\LaravelPayloadEditor\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use PhpNl\LaravelPayloadEditor\Contracts\FailedJobRepository;

class DatabaseFailedJobRepository implements FailedJobRepository
{
    /**
     * Database table name for failed jobs.
     */
    protected string $table;

    public function __construct()
    {
        $this->table = config('queue.failed.table', 'failed_jobs');
    }

    /**
     * Retrieve a failed job by its UUID.
     */
    public function find(string $uuid): ?object
    {
        return DB::table($this->table)->where('uuid', $uuid)->first();
    }

    /**
     * Retrieve a paginated list of failed jobs.
     *
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15): mixed
    {
        return DB::table($this->table)->orderByDesc('failed_at')->paginate($perPage);
    }

    /**
     * Update the payload of a failed job.
     */
    public function updatePayload(string $uuid, string $payload): bool
    {
        return DB::table($this->table)
            ->where('uuid', $uuid)
            ->update(['payload' => $payload]) > 0;
    }

    /**
     * Retry a failed job.
     */
    public function retry(string $uuid): bool
    {
        return Artisan::call('queue:retry', ['id' => $uuid]) === 0;
    }
}
