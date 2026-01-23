<div class="bg-white rounded-2xl shadow-2xl overflow-hidden" 
    x-data="{
        started: @entangle('installing'),
        progress: @entangle('installProgress'),
        complete: @entangle('installComplete'),
        async startInstall() {
            if (this.started) return;
            this.started = true;
            try {
                await $wire.installStep1_Env();
                // Allow file system to settle
                await new Promise(r => setTimeout(r, 1000));
                
                await $wire.installStep2_Migrate();
                await $wire.installStep3_Admin();
                await $wire.installStep4_Finalize();
            } catch (e) {
                console.error('Installation failed:', e);
                // Error handling is managed by Livewire component
            }
        }
    }"
    x-cloak>
    <!-- Header -->
    <div class="gradient-bg text-white px-8 py-6 text-center">
        @if(config('installer.logo'))
            <img src="{{ config('installer.logo') }}" alt="Logo" class="h-12 mx-auto mb-4">
        @endif
        <h1 class="text-3xl font-bold">{{ config('installer.name', 'Application') }}</h1>
        <p class="text-white/80 mt-1">Installation Wizard</p>
    </div>

    <!-- Progress -->
    <div class="px-8 py-4 bg-gray-50 border-b">
        <div class="flex justify-between">
            @foreach(['Requirements', 'Permissions', 'Database', 'Admin', 'Complete'] as $index => $label)
                <div class="flex items-center {{ $index < 4 ? 'flex-1' : '' }}">
                    <div class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-semibold transition-all
                        {{ $step > $index + 1 ? 'bg-green-500 text-white' : ($step == $index + 1 ? 'bg-primary text-white' : 'bg-gray-200 text-gray-500') }}">
                        @if($step > $index + 1) ✓ @else {{ $index + 1 }} @endif
                    </div>
                    <span class="ml-2 text-sm hidden sm:inline {{ $step >= $index + 1 ? 'text-gray-700' : 'text-gray-400' }}">{{ $label }}</span>
                    @if($index < 4)
                        <div class="flex-1 mx-4 h-0.5 {{ $step > $index + 1 ? 'bg-green-500' : 'bg-gray-200' }}"></div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div class="p-8">
        @if($installing || $installComplete)
            <div class="space-y-4">
                <!-- Progress Bar -->
                <div class="mb-4">
                    <div class="flex justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700">Installation Progress</span>
                        <span class="text-sm font-medium text-gray-700" x-text="progress + '%'"></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-primary h-2.5 rounded-full transition-all duration-500" :style="'width: ' + progress + '%'"></div>
                    </div>
                </div>

                <div class="bg-gray-900 text-green-400 p-4 rounded-lg font-mono text-sm h-64 overflow-y-auto shadow-inner">
                    @foreach($installLog as $message)
                        <div class="mb-1">> {{ $message }}</div>
                    @endforeach
                    @if($installError)
                        <div class="text-red-500 font-bold mt-2">Error: {{ $installError }}</div>
                    @endif
                </div>
                
                @if($installComplete)
                    <div class="text-center pt-4" x-show="complete" x-transition>
                        <div class="text-green-600 font-bold text-xl mb-2">🎉 Installation Successful!</div>
                        <p class="text-gray-600 mb-6">BookFlow is ready to use. You can now access your admin dashboard.</p>
                        <div class="space-y-3">
                            <a href="{{ config('installer.redirect_after_install', '/admin/dashboard') }}" 
                               class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark transition shadow-lg font-semibold">
                                Go to Dashboard →
                            </a>
                            <div class="text-sm text-gray-500">
                                <p>Default login: <strong>{{ $adminEmail }}</strong></p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @else
            @include('installer::steps.step-' . $step)
        @endif
    </div>

    <!-- Navigation -->
    @if($step < 5 && !$installComplete && !$installing)
        <div class="px-8 py-4 bg-gray-50 border-t flex justify-between">
            @if($step > 1)
                <button wire:click="prevStep" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    ← Back
                </button>
            @else
                <div></div>
            @endif
            
            @if($step < 4)
                <button wire:click="nextStep" 
                    class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition disabled:opacity-50 disabled:cursor-not-allowed"
                    @if(($step === 1 && !$requirementsPassed) || ($step === 2 && !$permissionsPassed) || ($step === 3 && !$dbConnected)) disabled @endif>
                    Continue →
                </button>
            @elseif($step === 4)
                <button x-on:click="startInstall" 
                    :disabled="started"
                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!started">Install Now</span>
                    <span x-show="started">Installing...</span>
                </button>
            @endif
        </div>
    @endif
</div>
