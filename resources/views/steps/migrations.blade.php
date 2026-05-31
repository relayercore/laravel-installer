<div class="mb-6">
    <h3 class="text-2xl font-bold text-slate-900">Database Setup</h3>
    <p class="text-slate-500 mt-2">Ready to install database tables.</p>
</div>

<div class="bg-slate-50 border border-slate-200 rounded-2xl p-6">
    <div class="flex items-start">
        <div class="flex-shrink-0 mt-1">
             <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
            </div>
        </div>
        <div class="ml-5">
            <h4 class="text-lg font-semibold text-slate-800">Migration & Seeding</h4>
            <p class="text-slate-600 mt-1 text-sm leading-relaxed">
                We're about to run the standard migration to set up your database schema. 
                You can optionally seed the database with demo content to get started quickly.
            </p>
            
            <div class="mt-6">
                <label class="flex items-center cursor-pointer group">
                    <div class="relative">
                        <input type="checkbox" wire:model="state.load_demo_data" class="sr-only">
                        <div class="block bg-slate-200 w-12 h-7 rounded-full transition duration-200 ease-in-out group-hover:bg-slate-300"></div>
                        <div class="dot absolute left-1 top-1 bg-white w-5 h-5 rounded-full shadow transition transform duration-200 ease-in-out" :class="{'translate-x-5 !bg-slate-900': $wire.state.load_demo_data}"></div>
                    </div>
                    <div class="ml-3 select-none">
                        <span class="block text-sm font-semibold text-slate-900">Install Demo Data</span>
                        <span class="block text-xs text-slate-500">Recommended for development</span>
                    </div>
                </label>
            </div>
        </div>
    </div>
</div>

<div x-data="{ showHelp: false }" class="mt-6">
    <button type="button" @click="showHelp = !showHelp" class="text-sm font-medium text-slate-500 hover:text-slate-700 underline decoration-slate-300 hover:decoration-slate-500 underline-offset-4 transition-all">
        <span x-show="!showHelp">Having trouble? Check common solutions</span>
        <span x-show="showHelp" style="display: none;">Hide solutions</span>
    </button>
    <div x-show="showHelp" style="display: none;" class="mt-3 p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800 space-y-2">
        <p>• Ensure your database server is running and accessible from this server.</p>
        <p>• Verify the database credentials in the previous step (host, port, username, password).</p>
        <p>• Make sure your database user has <code class="bg-amber-100 px-1 rounded">CREATE TABLE</code> privileges.</p>
        <p>• Check the database server error logs for more specific details.</p>
        <p>• If all else fails, review <code class="bg-amber-100 px-1 rounded">storage/logs/laravel.log</code> for the full error trace.</p>
    </div>
</div>
