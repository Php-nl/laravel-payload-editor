<div class="space-y-6">
    <div class="flex sm:justify-between flex-col sm:flex-row gap-4 items-start sm:items-end">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Failed Jobs</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">Inspect, edit, retry, or delete failed Laravel jobs.</p>
        </div>

        @if(!$inspectingJobUuid)
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <div class="relative max-w-sm w-full lg:max-w-md hidden sm:block">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="search" class="block w-full rounded-md border-0 py-1.5 pl-10 pr-3 text-gray-900 dark:text-white dark:bg-slate-800 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 placeholder:text-gray-400 dark:placeholder:text-slate-500 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:focus:ring-indigo-500 sm:text-sm sm:leading-6 transition" placeholder="Search UUID, Class, Exception...">
                </div>

                @if(count($selectedJobs) > 0)
                    <button wire:click="retrySelected" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition">
                        Retry ({{ count($selectedJobs) }})
                    </button>
                @endif

                <button wire:click="flushJobs" wire:confirm="Are you sure you want to delete ALL failed jobs? This cannot be undone." class="inline-flex items-center rounded-md bg-white dark:bg-slate-800 px-3 py-2 text-sm font-semibold text-red-600 dark:text-red-400 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 hover:bg-gray-50 dark:hover:bg-slate-750 transition">
                    Flush
                </button>
            </div>
        @endif
    </div>

    @if (session()->has('success'))
        <div class="rounded-md bg-green-50 dark:bg-green-900/30 p-4 shadow-sm border border-green-200 dark:border-green-800/50">
            <div class="flex">
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800 dark:text-green-300">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if($inspectingJobUuid)
        <!-- Editor View -->
        <div class="bg-white dark:bg-slate-800 shadow-[0_1px_3px_0_rgba(0,0,0,0.02)] sm:rounded-lg border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-start mb-5">
                    <div>
                        <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-white">Editing Payload</h3>
                        <div class="mt-1 max-w-xl text-sm text-gray-500 dark:text-slate-400 font-mono break-all">
                            ID: {{ $inspectedJob->uuid }}
                        </div>
                    </div>
                </div>

                @if($errorMessage)
                    <div class="rounded-md bg-red-50 dark:bg-red-900/30 p-4 mb-4 border border-red-200 dark:border-red-800/50">
                        <p class="text-sm font-medium text-red-800 dark:text-red-300">{{ $errorMessage }}</p>
                    </div>
                @endif

                <div class="border-t border-gray-200 dark:border-slate-700 pt-5 mt-5">
                    @if(empty($schema) && !$errorMessage)
                        <div class="rounded-md bg-gray-50 dark:bg-slate-700/50 p-4 border border-gray-200 dark:border-slate-600 mt-4">
                            <p class="text-sm text-gray-500 dark:text-slate-400 text-center">No editable properties found via Reflection on this job.</p>
                        </div>
                    @endif

                    <div class="space-y-6">
                        @foreach($schema as $property => $meta)
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
                                <div class="md:col-span-1 pt-1.5 break-words">
                                    <label class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-200">
                                        {{ $property }} 
                                    </label>
                                    <span class="inline-flex items-center rounded-md bg-gray-50 dark:bg-slate-700 px-2 py-1 text-xs font-medium text-gray-600 dark:text-slate-300 ring-1 ring-inset ring-gray-500/10 dark:ring-slate-500/30 mt-1 font-mono transition">{{ $meta['type'] }}</span>
                                </div>
                                <div class="md:col-span-2">
                                    @if($meta['editable'])
                                        @if($meta['type'] === 'bool')
                                            <select wire:model="form.{{ $property }}" class="block w-full max-w-md rounded-md border-0 py-2.5 pl-3 pr-10 text-gray-900 dark:text-white dark:bg-slate-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:focus:ring-indigo-500 sm:text-sm sm:leading-6 transition">
                                                <option value="1">True</option>
                                                <option value="0">False</option>
                                            </select>
                                        @else
                                            <input type="text" wire:model="form.{{ $property }}" class="block w-full max-w-md rounded-md border-0 py-2.5 px-3 text-gray-900 dark:text-white dark:bg-slate-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:focus:ring-indigo-500 sm:text-sm sm:leading-6 transition">
                                        @endif
                                    @else
                                        <div class="bg-gray-50 dark:bg-slate-700/50 text-gray-500 dark:text-slate-400 border border-gray-200 dark:border-slate-600 rounded-md py-2.5 px-3 text-sm max-w-md flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-gray-400 dark:text-slate-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                            <span class="truncate">Not directly editable. ({{ $meta['type'] }})</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-slate-700/30 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-gray-200 dark:border-slate-700 items-center">
                <button wire:click="saveAndRetry" type="button" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto transition">
                    <svg class="w-4 h-4 mr-2 -ml-1 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Save & Requeue
                </button>
                <button wire:click="close" type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-slate-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-200 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-600 hover:bg-gray-50 dark:hover:bg-slate-700 sm:mt-0 sm:w-auto transition">Cancel</button>
            </div>
        </div>
    @else
        <!-- List View -->
        <div class="bg-white dark:bg-slate-800 shadow-[0_1px_3px_0_rgba(0,0,0,0.02)] border border-gray-200 dark:border-slate-700 sm:rounded-lg overflow-hidden flex flex-col min-h-[400px]">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700/60">
                    <thead class="bg-gray-50 dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700">
                        <tr>
                            <th scope="col" class="pl-4 py-3.5 w-10"></th>
                            <th scope="col" class="py-3.5 pl-3 pr-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Job Details</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Failed At</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-slate-700/60 bg-white dark:bg-slate-800">
                        @forelse($jobs as $job)
                            @php $payload = json_decode($job->payload); @endphp
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-slate-700/30 transition group" wire:key="job-{{ $job->uuid }}">
                                <td class="pl-4 py-4 whitespace-nowrap">
                                    <input type="checkbox" wire:model="selectedJobs" value="{{ $job->uuid }}" class="w-4 h-4 text-indigo-600 bg-white dark:bg-slate-900 border-gray-300 dark:border-slate-600 rounded focus:ring-indigo-600 dark:focus:ring-indigo-500">
                                </td>
                                <td class="whitespace-nowrap py-4 pl-3 pr-3 text-sm">
                                    <div class="flex flex-col">
                                        <div class="font-medium text-indigo-600 dark:text-indigo-400 font-mono">{{ $payload->displayName ?? 'Unknown' }}</div>
                                        <div class="flex items-center mt-1">
                                            <span class="inline-flex items-center rounded bg-gray-100 dark:bg-slate-700 px-2 py-0.5 text-xs font-medium text-gray-600 dark:text-slate-300 mr-2">
                                                {{ $job->queue }}
                                            </span>
                                            <div class="text-gray-500 dark:text-slate-400 text-xs truncate max-w-md sm:max-w-xl" title="{{ $job->exception }}">{{ Str::limit($job->exception, 80) }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-slate-400">
                                    {{ \Carbon\Carbon::parse($job->failed_at)->diffForHumans() }}
                                    <span class="text-xs text-gray-400 dark:text-slate-500 block mt-0.5">{{ \Carbon\Carbon::parse($job->failed_at)->format('Y-m-d H:i:s') }}</span>
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button wire:click="retryJob('{{ $job->uuid }}')" class="inline-flex items-center p-1.5 text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 rounded-md hover:bg-indigo-50 dark:hover:bg-slate-700 transition" title="Quick Retry">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                        </button>
                                        <button wire:click="inspect('{{ $job->uuid }}')" class="inline-flex items-center p-1.5 text-gray-600 hover:text-gray-900 dark:text-slate-400 dark:hover:text-slate-200 rounded-md hover:bg-gray-100 dark:hover:bg-slate-700 transition" title="Inspect & Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>
                                        <button wire:click="deleteJob('{{ $job->uuid }}')" wire:confirm="Delete this failed job?" class="inline-flex items-center p-1.5 text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 rounded-md hover:bg-red-50 dark:hover:bg-slate-700 transition" title="Delete">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12">
                                    <div class="min-h-[300px] flex flex-col justify-center items-center w-full">
                                        <div class="rounded-full bg-gray-100 dark:bg-slate-700 p-3 mx-auto flex items-center justify-center w-14 h-14 mb-4">
                                            <svg class="h-8 w-8 text-gray-400 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">
                                            @if(!empty($search))
                                                No search results found
                                            @else
                                                No failed jobs
                                            @endif
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">
                                            @if(!empty($search))
                                                Try adjusting your search query.
                                            @else
                                                No failed queue jobs found. Good job!
                                            @endif
                                        </p>
                                        @if(!empty($search))
                                            <button wire:click="$set('search', '')" class="mt-4 text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-medium">Clear search</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($jobs->hasPages() || count($jobs) > 0)
                <div class="border-t border-gray-200 dark:border-slate-700 px-4 py-3 bg-gray-50 dark:bg-slate-800 sm:px-6 mt-auto">
                    {{ $jobs->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
