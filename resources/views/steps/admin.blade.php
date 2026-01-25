<div class="mb-6">
    <h3 class="text-2xl font-bold text-slate-900">Create Administrator</h3>
    <p class="text-slate-500 mt-2">Setup your primary access account.</p>
</div>

<div class="space-y-6">
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">Full Name</label>
        <input type="text" wire:model="state.name" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent transition-all shadow-sm" placeholder="John Doe">
    </div>
    
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">Email Address</label>
        <div class="relative">
             <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <input type="email" wire:model="state.email" class="w-full bg-white border border-slate-200 rounded-xl pl-11 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent transition-all shadow-sm" placeholder="admin@example.com">
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
            <input type="password" wire:model="state.password" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent transition-all shadow-sm" placeholder="Min. 8 characters">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Confirm Password</label>
            <input type="password" wire:model="state.password_confirmation" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent transition-all shadow-sm" placeholder="Repeat password">
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
