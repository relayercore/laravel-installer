<div class="mb-4">
    <h3 class="text-2xl font-bold text-slate-900">{{ __('installer::installer.permissions_title') }}</h3>
    <p class="text-slate-500 mt-2">{{ __('installer::installer.permissions_subtitle') }}</p>
</div>

<div class="space-y-3">
    @php $permissions = $step->check(); @endphp
    @foreach($permissions as $path => $writable)
        <div class="flex items-center p-4 rounded-xl border transition-all duration-200 {{ $writable ? 'bg-white border-slate-200 shadow-sm' : 'bg-red-50 border-red-200' }}">
            <div class="flex-shrink-0">
                @if($writable)
                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                @else
                    <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                @endif
            </div>
            <div class="ml-4 flex-1">
                <p class="text-sm font-mono font-semibold {{ $writable ? 'text-slate-700' : 'text-red-700' }}">{{ $path }}</p>
            </div>
            <div class="ml-4">
                <span class="text-xs font-semibold px-2 py-1 rounded {{ $writable ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $writable ? __('installer::installer.permissions_writable') : __('installer::installer.permissions_fix') }}
                </span>
            </div>
        </div>
    @endforeach
</div>

@if(in_array(false, $permissions))
    <div class="mt-6 p-4 bg-red-100 text-red-700 rounded-lg">
        <strong>{{ __('installer::installer.permissions_action_needed') }}</strong>
    </div>
@else
    <div class="mt-6 p-4 bg-green-100 text-green-700 rounded-lg">
        <strong>{{ __('installer::installer.permissions_all_correct') }}</strong>
    </div>
@endif
