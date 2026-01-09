<div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
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
        @include('installer::steps.step-' . $step)
    </div>

    <!-- Navigation -->
    @if($step < 5 && !$installComplete)
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
                <button wire:click="install" 
                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition disabled:opacity-50"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="install">Install Now</span>
                    <span wire:loading wire:target="install">Installing...</span>
                </button>
            @endif
        </div>
    @endif
</div>
