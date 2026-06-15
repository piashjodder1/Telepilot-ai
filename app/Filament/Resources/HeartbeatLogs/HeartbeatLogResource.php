<?php

namespace App\Filament\Resources\HeartbeatLogs;

use App\Filament\Resources\HeartbeatLogs\Pages\CreateHeartbeatLog;
use App\Filament\Resources\HeartbeatLogs\Pages\EditHeartbeatLog;
use App\Filament\Resources\HeartbeatLogs\Pages\ListHeartbeatLogs;
use App\Filament\Resources\HeartbeatLogs\Schemas\HeartbeatLogForm;
use App\Filament\Resources\HeartbeatLogs\Tables\HeartbeatLogsTable;
use App\Models\HeartbeatLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HeartbeatLogResource extends Resource
{
    protected static string|\UnitEnum|null $navigationGroup = 'Settings';
    
    

    public static function form(Schema $schema): Schema
    {
        return HeartbeatLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HeartbeatLogsTable::configure($table);
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
            'index' => ListHeartbeatLogs::route('/'),
            'create' => CreateHeartbeatLog::route('/create'),
            'edit' => EditHeartbeatLog::route('/{record}/edit'),
        ];
    }
}
