<h2 class="text-xl font-semibold mb-4">Server Requirements</h2>
<p class="text-gray-600 mb-6">Please ensure your server meets the following requirements.</p>

<div class="space-y-3">
    @foreach($requirements as $requirement => $passed)
        <div class="flex items-center justify-between p-3 rounded-lg {{ $passed ? 'bg-green-50' : 'bg-red-50' }}">
            <span class="font-medium">{{ $requirement }}</span>
            @if($passed)
                <span class="text-green-600 font-semibold">✓ Pass</span>
            @else
                <span class="text-red-600 font-semibold">✗ Fail</span>
            @endif
        </div>
    @endforeach
</div>

@if(!$requirementsPassed)
    <div class="mt-6 p-4 bg-red-100 text-red-700 rounded-lg">
        <strong>Action Required:</strong> Please resolve the failed requirements before continuing.
    </div>
@else
    <div class="mt-6 p-4 bg-green-100 text-green-700 rounded-lg">
        <strong>All requirements passed!</strong> Click Continue to proceed.
    </div>
@endif
