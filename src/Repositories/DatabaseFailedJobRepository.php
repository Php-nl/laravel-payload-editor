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
     * Retrieve a paginated list of failed jobs, optionally filtered by a search query.
     *
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, string $search = ''): mixed
    {
        $query = DB::table($this->table)->orderByDesc('failed_at');

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('uuid', 'like', "%{$search}%")
                    ->orWhere('queue', 'like', "%{$search}%")
                    ->orWhere('payload', 'like', "%{$search}%")
                    ->orWhere('exception', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
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

    /**
     * Delete a failed job by its UUID.
     */
    public function delete(string $uuid): bool
    {
        return DB::table($this->table)->where('uuid', $uuid)->delete() > 0;
    }

    /**
     * Delete all failed jobs from the repository.
     */
    public function flush(): bool
    {
        DB::table($this->table)->delete();
        
        return true;
    }
}
