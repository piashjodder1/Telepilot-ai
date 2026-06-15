<?php

namespace App\Filament\Resources\AiModels\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class AiModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Configuration Name (e.g. My Gemini Model)')
                    ->required(),
                Select::make('provider')
                    ->options([
                        'openai'      => 'OpenAI',
                        'gemini'      => 'Google Gemini',
                        'openrouter'  => 'OpenRouter',
                        'opencode'    => 'OpenCode (Legacy)',
                        'custom'      => 'Custom Provider',
                    ])
                    ->live()
                    ->required(),
                TextInput::make('model')
                    ->label('Model ID')
                    ->helperText(fn (Get $get) => match ($get('provider')) {
                        'openrouter' => 'e.g., google/gemini-1.5-flash, openai/gpt-4o, meta-llama/llama-3.1-8b-instruct:free',
                        'gemini'     => 'e.g., gemini-1.5-flash, gemini-1.5-pro, gemini-2.0-flash',
                        'openai'     => 'e.g., gpt-4o-mini, gpt-4o, gpt-3.5-turbo',
                        'custom'     => 'Enter the exact model ID for your custom provider',
                        default      => 'e.g. gpt-4o, gemini-1.5-flash, etc.'
                    })
                    ->required(),
                TextInput::make('api_key')
                    ->password()
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('base_url')
                    ->label('Base URL (API Endpoint)')
                    ->url()
                    ->visible(fn (Get $get) => $get('provider') === 'custom')
                    ->required(fn (Get $get) => $get('provider') === 'custom'),
                TextInput::make('temperature')
                    ->required()
                    ->numeric()
                    ->default(0.7),
                TextInput::make('max_tokens')
                    ->required()
                    ->numeric()
                    ->default(1000),
                TextInput::make('timeout_seconds')
                    ->required()
                    ->numeric()
                    ->default(25),
                TextInput::make('retry_attempts')
                    ->required()
                    ->numeric()
                    ->default(2),
                Toggle::make('is_default')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
