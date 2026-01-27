<div class="space-y-6">
    <div class="text-center">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Select Your Business Type
        </h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Choose the industry template that best fits your needs. This will customize the interface and features for your business.
        </p>
    </div>

    @php
        $stepInstance = $steps['industry'];
        $verticals = $stepInstance->getAvailableVerticals();
    @endphp

    @if(empty($verticals))
        <div class="rounded-md bg-yellow-50 p-4 dark:bg-yellow-900/20">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">No industry modules found.</h3>
                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                        <p>We couldn't detect any verticals in the <code>verticals/</code> directory. Proceeding with the Universal/Standard configuration.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Auto-select universal if empty -->
        <input type="hidden" wire:model="state.vertical" value="universal">
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <!-- Universal Option -->
            <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none dark:bg-gray-800 {{ ($state['vertical'] ?? '') === 'universal' ? 'border-indigo-500 ring-2 ring-indigo-500' : 'border-gray-300 dark:border-gray-700' }}">
                <input type="radio" wire:model.live="state.vertical" value="universal" class="sr-only">
                <span class="flex flex-1">
                    <span class="flex flex-col w-full">
                        @if($dScreen = $stepInstance->getDefaultScreenshot())
                            <img src="{{ $dScreen }}" alt="Universal Preview" class="mb-3 rounded-lg object-cover h-32 w-full border border-gray-100">
                        @endif
                        <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">Universal / Standard</span>
                        <span class="mt-1 flex items-center text-sm text-gray-500 dark:text-gray-400">For consultants, freelancers, and general booking needs.</span>
                    </span>
                </span>
                <svg class="h-5 w-5 text-indigo-600 {{ ($state['vertical'] ?? '') === 'universal' ? '' : 'hidden' }}" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
            </label>

            <!-- Vertical Options -->
            @foreach($verticals as $slug => $data)
                <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none dark:bg-gray-800 {{ ($state['vertical'] ?? '') === $slug ? 'border-indigo-500 ring-2 ring-indigo-500' : 'border-gray-300 dark:border-gray-700' }}">
                    <input type="radio" wire:model.live="state.vertical" value="{{ $slug }}" class="sr-only">
                    <span class="flex flex-1">
                        <span class="flex flex-col w-full">
                            @if(!empty($data['screenshots'][0]))
                                <img src="{{ asset($data['screenshots'][0]) }}" alt="{{ $data['name'] }} Preview" class="mb-3 rounded-lg object-cover h-32 w-full border border-gray-100">
                            @endif
                            <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ $data['name'] }}</span>
                            <span class="mt-1 flex items-center text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($data['description'], 60) }}</span>
                        </span>
                    </span>
                    <svg class="h-5 w-5 text-indigo-600 {{ ($state['vertical'] ?? '') === $slug ? '' : 'hidden' }}" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </label>
            @endforeach
        </div>
    @endif
</div>
