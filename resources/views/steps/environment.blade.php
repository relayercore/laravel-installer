<div class="mb-6">
    <h3 class="text-2xl font-bold text-slate-900">Database Connection</h3>
    <p class="text-slate-500 mt-2">Configure your database settings.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="col-span-full">
        <label class="block text-sm font-semibold text-slate-700 mb-2">Connection Type</label>
        <select wire:model.live="state.connection" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent transition-all shadow-sm">
            <option value="mysql">MySQL / MariaDB</option>
            <option value="pgsql">PostgreSQL</option>
            <option value="sqlite">SQLite</option>
        </select>
    </div>

    @if(($state['connection'] ?? 'mysql') !== 'sqlite')
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">Host</label>
        <input type="text" wire:model="state.host" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent transition-all shadow-sm" placeholder="127.0.0.1">
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">Port</label>
        <input type="text" wire:model="state.port" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent transition-all shadow-sm" placeholder="3306">
    </div>
    @endif

    <div class="col-span-full">
        <label class="block text-sm font-semibold text-slate-700 mb-2">Database Name</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                </svg>
            </div>
            <input type="text" wire:model="state.database" class="w-full bg-white border border-slate-200 rounded-xl pl-11 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent transition-all shadow-sm" placeholder="laravel_app">
        </div>
        <p class="text-xs text-slate-500 mt-1">If it doesn't exist, we'll attempt to create it.</p>
    </div>

    @if(($state['connection'] ?? 'mysql') !== 'sqlite')
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">Username</label>
        <input type="text" wire:model="state.username" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent transition-all shadow-sm" placeholder="root">
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
        <input type="password" wire:model="state.password" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent transition-all shadow-sm" placeholder="• • • • • • • •">
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
