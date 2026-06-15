<?php

namespace App\Filament\Resources\HeartbeatLogs\Pages;

use App\Filament\Resources\HeartbeatLogs\HeartbeatLogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHeartbeatLog extends EditRecord
{
    protected static string $resource = HeartbeatLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
