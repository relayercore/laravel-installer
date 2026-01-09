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

<button wire:click="testConnection" class="mt-6 px-6 py-2.5 bg-gray-700 text-white rounded-lg hover:bg-gray-800 transition">
    <span wire:loading.remove wire:target="testConnection">Test Connection</span>
    <span wire:loading wire:target="testConnection">Testing...</span>
</button>

@if($dbConnected)
    <div class="mt-4 p-4 bg-green-100 text-green-700 rounded-lg">
        <strong>✓ Connection successful!</strong> Database is ready.
    </div>
@endif

@if($dbError)
    <div class="mt-4 p-4 bg-red-100 text-red-700 rounded-lg">
        <strong>Connection failed:</strong> {{ $dbError }}
    </div>
@endif
