<?php

namespace App\Filament\Resources\Topics\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TopicForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('rule_id')
                    ->required()
                    ->numeric(),
                Textarea::make('topic')
                    ->required()
                    ->columnSpanFull(),
                DateTimePicker::make('last_used_at')->displayFormat('d M Y, h:i A'),
                TextInput::make('use_count')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
