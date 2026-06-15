<div class="space-y-6">
    <div class="text-center">
        <h2 class="fi-modal-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">Sign in to Telegram</h2>
        <p class="fi-modal-description mt-2 text-sm text-gray-500 dark:text-gray-400">
            @if($step === 1)
                Please enter your phone number in international format.
            @elseif($step === 2)
                We've sent a code to the Telegram app on your other device.
            @elseif($step === 3)
                Your account is protected with an additional password.
            @endif
        </p>
    </div>

    @if($step === 1)
        <div class="space-y-4">
            <div>
                <label for="phone" class="fi-fo-field-wrp-label inline-flex items-center gap-x-3 mb-2">
                    <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">Phone Number</span>
                </label>
                <x-filament::input.wrapper>
                    <x-filament::input type="text" wire:model.defer="phone" wire:keydown.enter="sendCode" id="phone" placeholder="+1234567890" required autofocus />
                </x-filament::input.wrapper>
                @error('phone') <p class="fi-fo-field-wrp-error-message text-sm text-danger-600 dark:text-danger-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mt-6">
                <x-filament::button wire:click="sendCode" class="w-full">
                    <span wire:loading.remove wire:target="sendCode">Send Code</span>
                    <span wire:loading wire:target="sendCode">Please wait...</span>
                </x-filament::button>
            </div>
        </div>
    @endif

    @if($step === 2)
        <div class="space-y-4">
            <div>
                <label for="code" class="fi-fo-field-wrp-label inline-flex items-center gap-x-3 mb-2">
                    <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">Login Code</span>
                </label>
                <x-filament::input.wrapper>
                    <x-filament::input type="text" wire:model.defer="code" wire:keydown.enter="verifyCode" id="code" required autofocus />
                </x-filament::input.wrapper>
                @error('code') <p class="fi-fo-field-wrp-error-message text-sm text-danger-600 dark:text-danger-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mt-6">
                <x-filament::button wire:click="verifyCode" class="w-full">
                    <span wire:loading.remove wire:target="verifyCode">Verify Code</span>
                    <span wire:loading wire:target="verifyCode">Verifying...</span>
                </x-filament::button>
            </div>
        </div>
    @endif

    @if($step === 3)
        <div class="space-y-4">
            <div>
                <label for="password" class="fi-fo-field-wrp-label inline-flex items-center gap-x-3 mb-2">
                    <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">2-Step Verification Password</span>
                </label>
                <x-filament::input.wrapper>
                    <x-filament::input type="password" wire:model.defer="password" wire:keydown.enter="verifyPassword" id="password" required autofocus />
                </x-filament::input.wrapper>
                @error('password') <p class="fi-fo-field-wrp-error-message text-sm text-danger-600 dark:text-danger-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mt-6">
                <x-filament::button wire:click="verifyPassword" class="w-full">
                    <span wire:loading.remove wire:target="verifyPassword">Submit Password</span>
                    <span wire:loading wire:target="verifyPassword">Logging in...</span>
                </x-filament::button>
            </div>
        </div>
    @endif
</div>
