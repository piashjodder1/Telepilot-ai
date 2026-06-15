<?php

namespace App\Filament\Resources\AiModels\Tables;

use App\Models\AiModel;
use App\Services\AI\AIProviderFactory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AiModelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Model Name')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('provider')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'openai' => 'success',
                        'gemini' => 'info',
                        'opencode' => 'warning',
                        'custom' => 'gray',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('model')
                    ->label('Model ID')
                    ->searchable(),
                IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('test_connection')
                    ->label('Test Connection')
                    ->icon('heroicon-o-signal')
                    ->color('info')
                    ->action(function (AiModel $record) {
                        try {
                            $provider = AIProviderFactory::make($record);
                            $response = $provider->generate("Please say exactly 'Connection Successful'");

                            if (strlen($response) > 0) {
                                Notification::make()
                                    ->success()
                                    ->title('✅ Connection Successful!')
                                    ->body('AI Response: ' . $response)
                                    ->send();
                            } else {
                                Notification::make()
                                    ->warning()
                                    ->title('Unexpected Response')
                                    ->body('The API connected but returned an empty response.')
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('❌ Connection Failed!')
                                ->body($e->getMessage())
                                ->persistent()
                                ->send();
                        }
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
