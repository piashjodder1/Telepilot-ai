<x-filament-widgets::widget class="h-full">
    <x-filament::section class="h-full flex flex-col justify-between">
        <div class="flex items-center gap-x-3 mb-4">
            <h2 class="text-lg font-bold">Quick Actions</h2>
        </div>
        
        <div class="space-y-4">
            <a href="{{ App\Filament\Resources\Rules\RuleResource::getUrl('create') }}" class="block w-full">
                <div class="flex items-center gap-x-3 p-3 rounded-xl bg-primary-50 hover:bg-primary-100 transition">
                    <div class="p-2 bg-white rounded-lg shadow-sm">
                        <x-heroicon-o-plus class="w-5 h-5 text-primary-600"/>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-primary-900">Create New Rule</h3>
                        <p class="text-xs text-primary-600">Add a new autopilot rule</p>
                    </div>
                </div>
            </a>
            
            <a href="{{ App\Filament\Resources\AiModels\AiModelResource::getUrl('index') }}" class="block w-full">
                <div class="flex items-center gap-x-3 p-3 rounded-xl bg-success-50 hover:bg-success-100 transition">
                    <div class="p-2 bg-white rounded-lg shadow-sm">
                        <x-heroicon-o-cpu-chip class="w-5 h-5 text-success-600"/>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-success-900">Test AI Connection</h3>
                        <p class="text-xs text-success-600">Test your AI model connection</p>
                    </div>
                </div>
            </a>
            
            <a href="{{ App\Filament\Resources\TelegramAccounts\TelegramAccountResource::getUrl('index') }}" class="block w-full">
                <div class="flex items-center gap-x-3 p-3 rounded-xl bg-warning-50 hover:bg-warning-100 transition">
                    <div class="p-2 bg-white rounded-lg shadow-sm">
                        <x-heroicon-o-paper-airplane class="w-5 h-5 text-warning-600"/>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-warning-900">Test Telegram Bot</h3>
                        <p class="text-xs text-warning-600">Test your bot token & channel</p>
                    </div>
                </div>
            </a>
            
            @php
                $isStopped = \Illuminate\Support\Facades\Cache::get('autopulse.emergency_stop', false);
            @endphp
            <button wire:click="toggleEmergencyStop" type="button" class="block w-full text-left cursor-pointer">
                <div class="flex items-center gap-x-3 p-3 rounded-xl transition {{ $isStopped ? 'bg-danger-600 text-white hover:bg-danger-700' : 'bg-danger-50 hover:bg-danger-100' }}">
                <div class="p-2 bg-white rounded-lg shadow-sm">
                    <x-heroicon-o-stop-circle class="w-5 h-5 {{ $isStopped ? 'text-danger-600' : 'text-danger-600' }}"/>
                </div>
                <div>
                    <h3 class="text-sm font-semibold {{ $isStopped ? 'text-white' : 'text-danger-900' }}">
                        {{ $isStopped ? 'Resume System' : 'Emergency Stop' }}
                    </h3>
                    <p class="text-xs {{ $isStopped ? 'text-danger-100' : 'text-danger-600' }}">
                        {{ $isStopped ? 'Click to resume publishing' : 'Stop all publishing immediately' }}
                    </p>
                </div>
                </div>
            </button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
