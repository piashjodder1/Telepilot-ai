<?php

namespace App\Filament\Resources\Rules\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Rule Name')
                    ->required(),
                \Filament\Forms\Components\Select::make('ai_model_id')
                    ->label('AI Model')
                    ->relationship('aiModel', 'name')
                    ->required(),
                \Filament\Forms\Components\Select::make('channel_id')
                    ->label('Target Channel/Group')
                    ->relationship('channel', 'title')
                    ->required(),
                Textarea::make('topic')
                    ->label('Topic / Instructions')
                    ->required()
                    ->columnSpanFull(),
                \Filament\Forms\Components\Select::make('tone')
                    ->options([
                        'professional' => 'Professional',
                        'casual' => 'Casual',
                        'humorous' => 'Humorous',
                        'formal' => 'Formal',
                        'bangla_casual' => 'Bangla Casual',
                    ])
                    ->required()
                    ->default('professional'),
                \Filament\Forms\Components\Select::make('language')
                    ->options([
                        'en' => 'English',
                        'bn' => 'Bangla',
                        'en_bn' => 'Mixed (Eng + Bangla)',
                    ])
                    ->required()
                    ->default('bn'),
                \Filament\Forms\Components\Select::make('format')
                    ->options([
                        'text' => 'Text Post',
                        'poll' => 'Poll',
                        'photo_caption' => 'Photo + Caption',
                    ])
                    ->required()
                    ->default('text'),
                \Filament\Forms\Components\Select::make('frequency')
                    ->options([
                        'every_1_hour' => 'Every 1 Hour',
                        'every_3_hours' => 'Every 3 Hours',
                        'every_6_hours' => 'Every 6 Hours',
                        'every_12_hours' => 'Every 12 Hours',
                        'once_daily' => 'Once Daily',
                        'twice_daily' => 'Twice Daily',
                        'custom_minutes' => 'Custom Minutes',
                    ])
                    ->required()
                    ->default('every_6_hours'),
                TextInput::make('custom_minutes')
                    ->numeric(),
                TimePicker::make('active_from')
                    ->required()
                    ->seconds(false)
                    ->displayFormat('h:i A')
                    ->format('H:i:s')
                    ->default('09:00:00'),
                TimePicker::make('active_until')
                    ->required()
                    ->seconds(false)
                    ->displayFormat('h:i A')
                    ->format('H:i:s')
                    ->default('23:00:00'),
                TextInput::make('max_per_day')
                    ->required()
                    ->numeric()
                    ->default(4),
                \Filament\Forms\Components\CheckboxList::make('days_active')
                    ->label('Days Active')
                    ->options([
                        'sat' => 'Saturday',
                        'sun' => 'Sunday',
                        'mon' => 'Monday',
                        'tue' => 'Tuesday',
                        'wed' => 'Wednesday',
                        'thu' => 'Thursday',
                        'fri' => 'Friday',
                    ])
                    ->columns(3)
                    ->columnSpanFull()
                    ->default(['sat','sun','mon','tue','wed','thu','fri']),
                Toggle::make('is_active')
                    ->required(),
                DateTimePicker::make('last_run_at')->displayFormat('d M Y, h:i A')
                    ->disabled()
                    ->dehydrated(false),
                DateTimePicker::make('next_run_at')->displayFormat('d M Y, h:i A')
                    ->disabled()
                    ->dehydrated(false),
            ]);
    }
}
