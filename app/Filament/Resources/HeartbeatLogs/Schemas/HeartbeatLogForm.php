<?php

namespace App\Filament\Resources\HeartbeatLogs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class HeartbeatLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DateTimePicker::make('ticked_at')->displayFormat('d M Y, h:i A')
                    ->required(),
                TextInput::make('rules_checked')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('jobs_dispatched')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('posts_published')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('errors')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('duration_ms')
                    ->numeric(),
            ]);
    }
}
