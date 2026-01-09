<h2 class="text-xl font-semibold mb-4">Directory Permissions</h2>
<p class="text-gray-600 mb-6">The following directories must be writable.</p>

<div class="space-y-3">
    @foreach($permissions as $path => $writable)
        <div class="flex items-center justify-between p-3 rounded-lg {{ $writable ? 'bg-green-50' : 'bg-red-50' }}">
            <span class="font-medium font-mono text-sm">{{ $path }}</span>
            @if($writable)
                <span class="text-green-600 font-semibold">✓ Writable</span>
            @else
                <span class="text-red-600 font-semibold">✗ Not Writable</span>
            @endif
        </div>
    @endforeach
</div>

@if(!$permissionsPassed)
    <div class="mt-6 p-4 bg-red-100 text-red-700 rounded-lg">
        <strong>Action Required:</strong> Set proper permissions (chmod 775) on the directories above.
    </div>
@else
    <div class="mt-6 p-4 bg-green-100 text-green-700 rounded-lg">
        <strong>All permissions correct!</strong> Click Continue to proceed.
    </div>
@endif
