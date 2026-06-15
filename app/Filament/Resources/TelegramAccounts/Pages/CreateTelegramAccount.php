<?php

namespace App\Filament\Resources\TelegramAccounts\Pages;

use App\Filament\Resources\TelegramAccounts\TelegramAccountResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTelegramAccount extends CreateRecord
{
    protected static string $resource = TelegramAccountResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }
}
