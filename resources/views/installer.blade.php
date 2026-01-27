    x-data="{
        loading: @entangle('loading'),
        error: null,
        isFinishing: false
    }"
    x-on:installer-finishing.window="isFinishing = true"
    x-cloak>
    
    <div class="w-[90%] max-w-[1920px] bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row min-h-[85vh]">
        <!-- Left Sidebar (Visual & Progress) -->
        <div class="w-full md:w-[350px] lg:w-[400px] bg-slate-900 relative flex flex-col justify-between p-10 text-white overflow-hidden flex-shrink-0">
            <!-- Background Decoration -->
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-blue-500 blur-3xl opacity-20 pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-96 h-96 rounded-full bg-purple-500 blur-3xl opacity-20 pointer-events-none"></div>
            
            <!-- Brand -->
            <div class="relative z-10">
                @if(config('installer.logo'))
                    <img src="{{ config('installer.logo') }}" alt="Logo" class="h-10 mb-8">
                @else
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <span class="text-xl font-bold tracking-tight">{{ config('installer.name', 'Installer') }}</span>
                    </div>
                @endif
                
                <h2 class="text-2xl font-bold leading-tight mb-2">
                    {{ $step->label() }}
                </h2>
                <p class="text-slate-400 text-sm">
                    Step {{ array_search($step->id(), array_keys($steps)) + 1 }} of {{ count($steps) }}
                </p>
            </div>

            <!-- Vertical Progress Steps -->
            <div class="relative z-10 mt-12 flex-1">
                <div class="space-y-6">
                    @php 
                        $currentFound = false; 
                    @endphp
                    @foreach($steps as $s)
                        @php 
                            $isCurrent = $s->id() === $step->id();
                            if ($isCurrent) $currentFound = true;
                            $isPast = !$currentFound && !$isCurrent;
                        @endphp
                        <div class="flex items-center group transition-all duration-300 {{ $isCurrent ? 'opacity-100 translate-x-1' : ($isPast ? 'opacity-50' : 'opacity-30') }}">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold border transition-all duration-300
                                {{ $isCurrent ? 'bg-white text-slate-900 border-white shadow-lg scale-110' : ($isPast ? 'bg-blue-600/20 border-blue-500/50 text-blue-200' : 'border-slate-700 text-slate-500') }}">
                                @if($isPast) ✓ @else {{ $loop->iteration }} @endif
                            </div>
                            <span class="ml-4 text-sm font-medium transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-300' }}">
                                {{ $s->label() }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Footer -->
            <div class="relative z-10 mt-8 text-xs text-slate-500">
                &copy; {{ date('Y') }} {{ config('installer.name') }}. All rights reserved.
            </div>
        </div>

        <!-- Right Content (Form) -->
        <div class="w-full md:w-2/3 bg-white p-8 md:p-12 flex flex-col relative">
            
            <!-- Error Alert -->
            @if($errors->has('global'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg animate-fade-in-down">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700 font-medium">
                                {{ $errors->first('global') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <form wire:submit.prevent="next" class="flex-1 flex flex-col justify-between h-full">
                <!-- Step Content Container with Transition -->
                <div class="space-y-6" 
                     x-data 
                     x-show="true" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4"
                     x-transition:enter-end="opacity-100 translate-y-0">
                     
                    @include($step->view())
                </div>

                <!-- Action Bar -->
                <div class="mt-12 flex items-center justify-end border-t border-gray-100 pt-6">
                    <button type="submit" 
                         wire:loading.attr="disabled"
                         :disabled="isFinishing"
                         class="group relative inline-flex items-center justify-center px-8 py-3 text-sm font-semibold text-white transition-all duration-200 bg-slate-900 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 disabled:opacity-70 disabled:cursor-not-allowed shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                         
                         <span wire:loading.remove wire:target="next" x-show="!isFinishing" class="flex items-center gap-2">
                             {{ $step->id() === 'admin' ? 'Complete Installation' : 'Continue' }}
                             <svg class="w-4 h-4 transition-transform duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                             </svg>
                         </span>

                         <span x-show="isFinishing" style="display: none;" class="flex items-center gap-2">
                             <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                 <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                 <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                             </svg>
                             Finalizing...
                         </span>
                         
                         <span wire:loading wire:target="next" x-show="!isFinishing" class="flex items-center gap-2">
                             <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                 <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                 <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                             </svg>
                             Processing...
                         </span>
                     </button>
                </div>
            </form>
        </div>
    </div>
</div>
