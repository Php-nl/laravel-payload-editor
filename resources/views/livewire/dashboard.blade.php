<div class="space-y-6">
    <div class="flex justify-between items-end">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">Failed Jobs</h2>
            <p class="mt-1 text-sm text-gray-500">Inspect, edit and retry failed Laravel jobs.</p>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="rounded-md bg-green-50 p-4 shadow-sm border border-green-200">
            <div class="flex">
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if($inspectingJobUuid)
        <!-- Editor View -->
        <div class="bg-white shadow-[0_1px_3px_0_rgba(0,0,0,0.02)] sm:rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-start mb-5">
                    <div>
                        <h3 class="text-lg font-semibold leading-6 text-gray-900">Editing Payload</h3>
                        <div class="mt-1 max-w-xl text-sm text-gray-500 font-mono">
                            ID: {{ $inspectedJob->uuid }}
                        </div>
                    </div>
                </div>

                @if($errorMessage)
                    <div class="rounded-md bg-red-50 p-4 mb-4 border border-red-200">
                        <p class="text-sm font-medium text-red-800">{{ $errorMessage }}</p>
                    </div>
                @endif

                <div class="border-t border-gray-200 pt-5 mt-5">
                    @if(empty($schema) && !$errorMessage)
                        <div class="rounded-md bg-gray-50 p-4 border border-gray-200 mt-4">
                            <p class="text-sm text-gray-500 text-center">No editable properties found via Reflection on this job.</p>
                        </div>
                    @endif

                    <div class="space-y-6">
                        @foreach($schema as $property => $meta)
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
                                <div class="md:col-span-1 pt-1.5">
                                    <label class="block text-sm font-medium leading-6 text-gray-900">
                                        {{ $property }} 
                                    </label>
                                    <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 mt-1 font-mono hover:bg-gray-100 transition">{{ $meta['type'] }}</span>
                                </div>
                                <div class="md:col-span-2">
                                    @if($meta['editable'])
                                        @if($meta['type'] === 'bool')
                                            <select wire:model="form.{{ $property }}" class="block w-full max-w-md rounded-md border-0 py-2.5 pl-3 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 transition bg-white">
                                                <option value="1">True</option>
                                                <option value="0">False</option>
                                            </select>
                                        @else
                                            <input type="text" wire:model="form.{{ $property }}" class="block w-full max-w-md rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 transition bg-white">
                                        @endif
                                    @else
                                        <div class="bg-gray-50 text-gray-500 border border-gray-200 rounded-md py-2.5 px-3 text-sm max-w-md flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                            Not directly editable. Nested {{ $meta['type'] }}.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-gray-200 items-center">
                <button wire:click="saveAndRetry" type="button" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto transition">
                    <svg class="w-4 h-4 mr-2 -ml-1 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Save & Requeue
                </button>
                <button wire:click="close" type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition">Cancel</button>
            </div>
        </div>
    @else
        <!-- List View -->
        <div class="bg-white shadow-[0_1px_3px_0_rgba(0,0,0,0.02)] border border-gray-200 rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider sm:pl-6">Job Class / Exception</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Failed At</th>
                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($jobs as $job)
                        @php $payload = json_decode($job->payload); @endphp
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                <div class="font-medium text-indigo-600 font-mono">{{ $payload->displayName ?? 'Unknown' }}</div>
                                <div class="text-gray-500 text-xs mt-1 truncate max-w-xl" title="{{ $job->exception }}">{{ Str::limit($job->exception, 80) }}</div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($job->failed_at)->diffForHumans() }}
                                <span class="text-xs text-gray-400 block mt-0.5">{{ \Carbon\Carbon::parse($job->failed_at)->format('Y-m-d H:i:s') }}</span>
                            </td>
                            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                <button wire:click="inspect('{{ $job->uuid }}')" class="inline-flex items-center rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition">
                                    <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    Inspect
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center">
                                <div class="rounded-full bg-gray-100 p-3 mx-auto flex items-center justify-center w-14 h-14 mb-4">
                                    <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <h3 class="mt-2 text-sm font-semibold text-gray-900">No failed jobs</h3>
                                <p class="mt-1 text-sm text-gray-500">Geen gefaalde queue jobs gevonden. Goed bezig!</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($jobs->hasPages())
                <div class="border-t border-gray-200 px-4 py-3 bg-gray-50 sm:px-6">
                    {{ $jobs->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
