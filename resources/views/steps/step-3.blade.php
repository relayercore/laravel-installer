<h2 class="text-xl font-semibold mb-4">Database Configuration</h2>
<p class="text-gray-600 mb-6">Enter your database connection details.</p>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Connection Type</label>
        <select wire:model="dbConnection" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary/50 focus:border-primary">
            <option value="mysql">MySQL / MariaDB</option>
            <option value="pgsql">PostgreSQL</option>
            <option value="sqlite">SQLite</option>
        </select>
    </div>

    @if($dbConnection !== 'sqlite')
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Host</label>
        <input type="text" wire:model="dbHost" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary/50 focus:border-primary" placeholder="127.0.0.1">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Port</label>
        <input type="text" wire:model="dbPort" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary/50 focus:border-primary" placeholder="3306">
    </div>
    @endif

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Database Name</label>
        <input type="text" wire:model="dbDatabase" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary/50 focus:border-primary" placeholder="my_database">
    </div>

    @if($dbConnection !== 'sqlite')
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
        <input type="text" wire:model="dbUsername" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary/50 focus:border-primary" placeholder="root">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <input type="password" wire:model="dbPassword" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary/50 focus:border-primary" placeholder="••••••••">
    </div>
    @endif
</div>

<div class="mt-8 pt-6 border-t border-gray-100">
    <label class="flex items-center cursor-pointer group">
        <div class="relative">
            <input type="checkbox" wire:model="loadDemoData" class="sr-only">
            <div class="block bg-gray-200 w-12 h-7 rounded-full transition group-hover:bg-gray-300"></div>
            <div class="dot absolute left-1 top-1 bg-white w-5 h-5 rounded-full transition transform {{ $loadDemoData ? 'translate-x-5 !bg-primary' : '' }}"></div>
        </div>
        <div class="ml-3 text-gray-700 font-medium select-none">
            Load Demo Data <span class="text-xs text-gray-400 ml-1">(Recommended for testing)</span>
        </div>
    </label>
    <p class="mt-2 text-sm text-gray-500 ml-15">
        Populates the database with sample data (services, staff, and example bookings) so you can explore the application immediately.
    </p>
</div>

<button wire:click="testConnection" class="mt-6 px-6 py-2.5 bg-gray-700 text-white rounded-lg hover:bg-gray-800 transition">
    <span wire:loading.remove wire:target="testConnection">Test Connection</span>
    <span wire:loading wire:target="testConnection">Testing...</span>
</button>

@if($dbConnected)
    <div class="mt-4 p-4 bg-green-100 text-green-700 rounded-lg">
        <strong>✓ Connection successful!</strong> Database is ready.
    </div>
@endif

@if($dbFriendlyError)
    <div class="mt-4 p-4 bg-red-100 text-red-700 rounded-lg" x-data="{ showDetails: false }">
        <p class="font-semibold mb-1">Connection failed</p>
        <p class="text-sm mb-2">{{ $dbFriendlyError }}</p>

        @if($dbError)
            <button type="button" @click="showDetails = !showDetails" class="text-xs text-red-700 underline">
                <span x-show="!showDetails">Show technical details</span>
                <span x-show="showDetails">Hide technical details</span>
            </button>
            <pre class="mt-2 text-xs bg-red-200/60 text-red-800 rounded p-2 font-mono" x-show="showDetails">{{ $dbError }}</pre>
        @endif
    </div>
@endif
