<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Http;

class AppSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog';
    protected static string|\UnitEnum|null $navigationGroup = 'Settings';
    protected static ?string $title = 'Settings';

    protected string $view = 'filament.pages.app-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'system_bot_token'  => Setting::get('system_bot_token', ''),
            'admin_chat_id'     => Setting::get('admin_chat_id', ''),
            'telegram_api_id'   => Setting::get('telegram_api_id', env('TELEGRAM_API_ID', '')),
            'telegram_api_hash' => Setting::get('telegram_api_hash', env('TELEGRAM_API_HASH', '')),
            'emergency_stop'    => \Illuminate\Support\Facades\Cache::get('autopulse_emergency_stop', false),
            'timezone'          => Setting::get('timezone', 'Asia/Dhaka'),
        ]);
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Tabs::make('Settings')
                    ->tabs([
                        \Filament\Schemas\Components\Tabs\Tab::make('System Preferences')
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->schema([
                                \Filament\Forms\Components\Toggle::make('emergency_stop')
                                    ->label('Emergency Stop (Kill Switch)')
                                    ->helperText('Turn this ON to instantly halt all background posting and AI generation.')
                                    ->onColor('danger'),
                                \Filament\Forms\Components\Select::make('timezone')
                                    ->label('System Timezone')
                                    ->helperText('Select your local timezone so schedules trigger correctly.')
                                    ->options([
                                        'Asia/Dhaka' => 'Asia/Dhaka (Bangladesh)',
                                        'Asia/Kolkata' => 'Asia/Kolkata (India)',
                                        'UTC' => 'UTC (Universal)',
                                        'America/New_York' => 'America/New_York (EST)',
                                        'Europe/London' => 'Europe/London (GMT)',
                                    ])
                                    ->default('Asia/Dhaka')
                                    ->required(),
                            ]),

                        \Filament\Schemas\Components\Tabs\Tab::make('Telegram Core API')
                            ->icon('heroicon-o-key')
                            ->schema([
                                TextInput::make('telegram_api_id')
                                    ->label('API ID')
                                    ->numeric()
                                    ->required(),
                                TextInput::make('telegram_api_hash')
                                    ->label('API Hash')
                                    ->password()
                                    ->required(),
                            ]),

                        \Filament\Schemas\Components\Tabs\Tab::make('System Bot Setup')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                TextInput::make('system_bot_token')
                                    ->label('System Bot Token')
                                    ->helperText('Create a bot using @BotFather and paste the token here.')
                                    ->password()
                                    ->required(),
                                TextInput::make('admin_chat_id')
                                    ->label('Admin Chat ID')
                                    ->helperText('Your personal Telegram chat ID where the bot will send drafts.')
                                    ->numeric()
                                    ->required(),
                            ]),
                    ])
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::set('system_bot_token', $data['system_bot_token']);
        Setting::set('admin_chat_id', $data['admin_chat_id']);
        Setting::set('telegram_api_id', $data['telegram_api_id']);
        Setting::set('telegram_api_hash', $data['telegram_api_hash']);
        Setting::set('timezone', $data['timezone']);
        
        \Illuminate\Support\Facades\Cache::put('autopulse_emergency_stop', $data['emergency_stop']);

        // Set Webhook automatically
        try {
            $token = $data['system_bot_token'];
            $webhookUrl = url('/telegram/webhook');
            $response = Http::post("https://api.telegram.org/bot{$token}/setWebhook", [
                'url' => $webhookUrl
            ]);

            if ($response->successful() && $response->json('ok')) {
                Notification::make()
                    ->title('Settings Saved')
                    ->body('Settings saved and Webhook connected successfully!')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Settings Saved, but Webhook Failed')
                    ->body($response->body())
                    ->warning()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Settings Saved, but Webhook Failed')
                ->body($e->getMessage())
                ->warning()
                ->send();
        }
    }
}
