<?php

namespace App\Filament\Resources\HeartbeatLogs\Pages;

use App\Filament\Resources\HeartbeatLogs\HeartbeatLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHeartbeatLog extends CreateRecord
{
    protected static string $resource = HeartbeatLogResource::class;
}
