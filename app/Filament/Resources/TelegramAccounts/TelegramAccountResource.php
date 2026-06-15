<?php

namespace App\Filament\Resources\TelegramAccounts;

use App\Filament\Resources\TelegramAccounts\Pages\CreateTelegramAccount;
use App\Filament\Resources\TelegramAccounts\Pages\EditTelegramAccount;
use App\Filament\Resources\TelegramAccounts\Pages\ListTelegramAccounts;
use App\Filament\Resources\TelegramAccounts\Schemas\TelegramAccountForm;
use App\Filament\Resources\TelegramAccounts\Tables\TelegramAccountsTable;
use App\Models\TelegramAccount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TelegramAccountResource extends Resource
{
    protected static string|\UnitEnum|null $navigationGroup = 'Telegram';
    

    public static function form(Schema $schema): Schema
    {
        return TelegramAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TelegramAccountsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTelegramAccounts::route('/'),
            'create' => CreateTelegramAccount::route('/create'),
            'edit' => EditTelegramAccount::route('/{record}/edit'),
        ];
    }
}
