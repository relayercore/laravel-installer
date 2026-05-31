<div class="mb-6">
    <h3 class="text-2xl font-bold text-slate-900">Database Connection</h3>
    <p class="text-slate-500 mt-2">Configure your database settings.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="col-span-full">
        <label class="block text-sm font-semibold text-slate-700 mb-2">Connection Type</label>
        <select wire:model.live="state.connection"
            x-on:change="
                const port = { mysql: '3306', pgsql: '5432', sqlsrv: '1433', sqlite: '' };
                $wire.state.port = port[$event.target.value] || '3306';
            "
            class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent transition-all shadow-sm">
            <option value="mysql">MySQL / MariaDB</option>
            <option value="pgsql">PostgreSQL</option>
            <option value="sqlite">SQLite</option>
            <option value="sqlsrv">SQL Server</option>
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
    
    <div class="col-span-full border-t border-slate-200 pt-6 mt-2">
         <label class="flex items-start gap-4 cursor-pointer group">
             <div class="flex items-center h-6">
                 <input type="checkbox" wire:model.live="state.multi_tenant" class="h-5 w-5 rounded-lg border-slate-300 text-indigo-600 focus:ring-indigo-600 transition-all">
             </div>
             <div class="flex-1">
                  <span class="font-semibold text-slate-900 block group-hover:text-indigo-600 transition-colors">Enable Multi-Tenancy (SaaS Mode)</span>
                  <span class="text-slate-500 text-sm leading-relaxed mt-1 block">Enable this if you plan to host multiple separate businesses under one installation. <strong class="font-medium text-slate-700">Leave unchecked</strong> for a standard single-business setup.</span>
             </div>
         </label>
    </div>
    
    <div class="col-span-full pt-4">
        <div class="flex items-center gap-4">
            <button type="button" wire:click="testDatabase" wire:loading.attr="disabled" class="text-sm font-semibold text-slate-700 hover:text-slate-900 underline decoration-slate-300 hover:decoration-slate-900 underline-offset-4 transition-all">
                <span wire:loading.remove wire:target="testDatabase">Test Connection</span>
                <span wire:loading wire:target="testDatabase">Testing...</span>
            </button>
        </div>

        @if($testConnectionResult)
            <div class="mt-4 p-4 rounded-xl {{ $testConnectionResult['success'] ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }} text-sm font-medium flex items-start gap-3">
                <div class="flex-shrink-0 mt-0.5">
                    @if($testConnectionResult['success'])
                        <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    @else
                        <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    @endif
                </div>
                <span>{{ $testConnectionResult['message'] }}</span>
            </div>
        @endif
    </div>
</div>

