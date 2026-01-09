<h2 class="text-xl font-semibold mb-4">Create Admin Account</h2>
<p class="text-gray-600 mb-6">Set up the administrator account.</p>

<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
        <input type="text" wire:model="adminName" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary/50 focus:border-primary" placeholder="John Doe">
        @error('adminName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
        <input type="email" wire:model="adminEmail" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary/50 focus:border-primary" placeholder="admin@example.com">
        @error('adminEmail') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input type="password" wire:model="adminPassword" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary/50 focus:border-primary" placeholder="Minimum 8 characters">
            @error('adminPassword') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
            <input type="password" wire:model="adminPasswordConfirm" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary/50 focus:border-primary" placeholder="Repeat password">
            @error('adminPasswordConfirm') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>
</div>

@if(!empty($installLog))
    <div class="mt-6 p-4 bg-gray-900 text-gray-100 rounded-lg max-h-48 overflow-y-auto font-mono text-sm">
        @foreach($installLog as $log)
            <div>{{ $log }}</div>
        @endforeach
    </div>
@endif

@if($installError)
    <div class="mt-4 p-4 bg-red-100 text-red-700 rounded-lg">
        <strong>Installation Error:</strong> {{ $installError }}
    </div>
@endif
