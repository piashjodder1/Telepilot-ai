<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Manual Post Data')
                    ->description('Required fields for manual posting')
                    ->schema([
                        \Filament\Forms\Components\Select::make('channel_id')
                            ->label('Target Channel/Group')
                            ->relationship('channel', 'title')
                            ->required()
                            ->searchable(),
                        \Filament\Forms\Components\Select::make('format')
                            ->options([
                                'text' => 'Text Post',
                                'poll' => 'Poll',
                                'photo_caption' => 'Photo + Caption',
                            ])
                            ->required()
                            ->default('text')
                            ->live(),
                        \Filament\Forms\Components\FileUpload::make('image_path')
                            ->label('Upload Image')
                            ->image()
                            ->directory('posts')
                            ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('format') === 'photo_caption'),
                        \Filament\Forms\Components\TextInput::make('poll_question')
                            ->label('Poll Question')
                            ->required(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('format') === 'poll')
                            ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('format') === 'poll')
                            ->columnSpanFull(),
                        \Filament\Forms\Components\TagsInput::make('poll_options')
                            ->label('Poll Options')
                            ->placeholder('Type an option and press Enter')
                            ->required(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('format') === 'poll')
                            ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('format') === 'poll')
                            ->columnSpanFull(),
                        \Filament\Forms\Components\Textarea::make('content')
                            ->required(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('format') !== 'poll')
                            ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('format') !== 'poll')
                            ->columnSpanFull(),
                        \Filament\Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'scheduled' => 'Scheduled',
                                'published' => 'Published',
                                'failed' => 'Failed',
                            ])
                            ->required()
                            ->default('draft'),
                        \Filament\Forms\Components\Textarea::make('fail_reason')
                            ->disabled()
                            ->columnSpanFull(),
                    ])->columns(1)->columnSpan('full'),

                \Filament\Schemas\Components\Section::make('AI Auto Data')
                    ->description('Data generated automatically by the AI Engine')
                    ->hidden()
                    ->schema([
                        \Filament\Forms\Components\Select::make('ai_model_id')
                            ->label('AI Model (Optional)')
                            ->relationship('aiModel', 'name')
                            ->searchable(),
                        \Filament\Forms\Components\Select::make('rule_id')
                            ->label('Associated Rule (Optional)')
                            ->relationship('rule', 'name')
                            ->searchable(),
                        \Filament\Forms\Components\Textarea::make('topic_used')
                            ->label('Topic/Instructions Used')
                            ->columnSpanFull()
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('ai_tokens_used')
                            ->numeric()
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('attempts')
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                        \Filament\Forms\Components\Hidden::make('content_hash')
                            ->default(fn () => md5(uniqid())),
                    ])->columns(2)->collapsed(),
            ])->columns(1);
    }
}
