<?php

namespace App\Filament\Resources\TelegramAccounts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TelegramAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('phone_number')
                    ->label('Phone Number')
                    ->placeholder('+8801700000000')
                    ->required()
                    ->helperText('Include country code, e.g., +88017...')
                    ->maxLength(20),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ]);
    }
}
