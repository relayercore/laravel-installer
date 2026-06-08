<div class="mb-6">
    <h3 class="section-title">{{ __('installer::installer.migrations_title') }}</h3>
    <p class="section-subtitle">{{ __('installer::installer.migrations_subtitle') }}</p>
</div>

<div class="migration-card flex items-start gap-4">
    <div class="migration-card-icon">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
        </svg>
    </div>
    <div>
        <h4 class="migration-card-heading">{{ __('installer::installer.migrations_heading') }}</h4>
        <p class="migration-card-desc">
            {{ __('installer::installer.migrations_description') }}
        </p>

        <div class="mt-6">
            <label class="flex items-center gap-4" style="cursor: pointer;">
                <div class="toggle-switch" wire:click.stop>
                    <input type="checkbox" wire:model.live="state.load_demo_data" class="sr-only" style="position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0,0,0,0);">
                    <div class="toggle-track" :class="{ 'toggle-track--active': $wire.state.load_demo_data }"></div>
                    <div class="toggle-thumb" :class="{ 'toggle-thumb--active': $wire.state.load_demo_data }"></div>
                </div>
                <div>
                    <span class="toggle-text">{{ __('installer::installer.migrations_demo_label') }}</span>
                    <span class="toggle-desc">{{ __('installer::installer.migrations_demo_hint') }}</span>
                </div>
            </label>
        </div>
    </div>
</div>

<div x-data="{ showHelp: false }" class="mt-6">
    <button type="button" @click="showHelp = !showHelp" class="help-toggle">
        <span x-show="!showHelp">{{ __('installer::installer.migrations_help_toggle_show') }}</span>
        <span x-show="showHelp" style="display: none;">{{ __('installer::installer.migrations_help_toggle_hide') }}</span>
    </button>
    <div x-show="showHelp" style="display: none;" class="help-box">
        <p>&bull; {{ __('installer::installer.migrations_help_1') }}</p>
        <p>&bull; {{ __('installer::installer.migrations_help_2') }}</p>
        <p>&bull; {{ __('installer::installer.migrations_help_3') }}</p>
        <p>&bull; {{ __('installer::installer.migrations_help_4') }}</p>
        <p>&bull; {{ __('installer::installer.migrations_help_5') }}</p>
    </div>
</div>
