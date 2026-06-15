<?php

namespace App\Filament\Resources\Groups\Schemas;

use App\Models\TelegramAccount;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('account_id')
                    ->label('Telegram Account')
                    ->options(
                        TelegramAccount::where('is_active', true)
                            ->get()
                            ->mapWithKeys(fn ($a) => [
                                $a->id => $a->full_name . ' (' . ($a->login_status === 'logged_in' ? '✅ Logged In' : '❌ ' . $a->login_status) . ')'
                            ])
                    )
                    ->required()
                    ->searchable()
                    ->columnSpanFull()
                    ->helperText('শুধু "Logged In" Account দিয়ে MadelineProto পোস্ট হবে।'),

                TextInput::make('title')
                    ->label('Group / Group Name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('chat_id')
                    ->label('Chat ID')
                    ->required()
                    ->helperText('উদাহরণ: -1001234567890 — "Sync Groups" বাটনে ক্লিক করলে অটো ভরে যাবে।'),

                TextInput::make('username')
                    ->label('Username (optional)')
                    ->placeholder('@groupname')
                    ->maxLength(100),

                Select::make('type')
                    ->label('Type')
                    ->options([
                        'group'    => '📢 Group',
                        'group'      => '👥 Group',
                        'supergroup' => '👥 Supergroup',
                    ])
                    ->required()
                    ->default('group'),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->required(),
            ]);
    }
}
