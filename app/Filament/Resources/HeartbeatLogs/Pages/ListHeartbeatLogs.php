<?php

namespace App\Filament\Resources\HeartbeatLogs\Pages;

use App\Filament\Resources\HeartbeatLogs\HeartbeatLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHeartbeatLogs extends ListRecords
{
    protected static string $resource = HeartbeatLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('clear_logs')
                ->label('Clear All Logs')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Clear All Heartbeat Logs')
                ->modalDescription('Are you sure you want to delete all heartbeat logs? This action cannot be undone.')
                ->modalSubmitActionLabel('Yes, delete all')
                ->action(function () {
                    \App\Models\HeartbeatLog::truncate();
                    \Filament\Notifications\Notification::make()
                        ->title('All heartbeat logs have been cleared successfully.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
