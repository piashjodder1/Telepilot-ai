<?php

namespace App\Filament\Resources\TelegramAccounts\Pages;

use App\Filament\Resources\TelegramAccounts\TelegramAccountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTelegramAccounts extends ListRecords
{
    protected static string $resource = TelegramAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('addAccount')
                ->label('New telegram account')
                ->icon('heroicon-o-plus')
                ->modalContent(fn () => view('telegram-login-modal'))
                ->modalSubmitAction(false)
                ->modalCancelAction(false)
                ->modalWidth('md'),
        ];
    }
}
