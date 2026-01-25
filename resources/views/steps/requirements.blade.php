<div class="mb-4">
    <h3 class="text-2xl font-bold text-slate-900">Server Requirements</h3>
    <p class="text-slate-500 mt-2">Checking your server compatibility.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-3">
    @php $requirements = $step->check(); @endphp
    @foreach($requirements as $requirement => $passed)
        <div class="flex items-center p-4 rounded-xl border transition-all duration-200 {{ $passed ? 'bg-white border-slate-200 shadow-sm' : 'bg-red-50 border-red-200' }}">
            <div class="flex-shrink-0">
                @if($passed)
                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                @else
                    <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                @endif
            </div>
            <div class="ml-4">
                <p class="text-sm font-semibold {{ $passed ? 'text-slate-700' : 'text-red-700' }}">{{ $requirement }}</p>
                <p class="text-xs {{ $passed ? 'text-slate-400' : 'text-red-500' }}">{{ $passed ? 'Passed' : 'Action Required' }}</p>
            </div>
        </div>
    @endforeach
</div>

@if(!in_array(false, $requirements))
    <div class="mt-6 p-4 bg-green-100 text-green-700 rounded-lg">
        <strong>All requirements passed!</strong> Click Continue to proceed.
    </div>
@else
    <div class="mt-6 p-4 bg-red-100 text-red-700 rounded-lg">
        <strong>Action Required:</strong> Please resolve the failed requirements before continuing.
    </div>
@endif
