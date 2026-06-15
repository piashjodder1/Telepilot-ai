<?php

namespace App\Filament\Resources\Rules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Rule Name')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('aiModel.name')
                    ->label('AI Model')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('channel.title')
                    ->label('Target Channel/Group')
                    ->badge()
                    ->color('success')
                    ->sortable(),
                TextColumn::make('frequency')
                    ->badge()
                    ->color('gray')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
                TextColumn::make('next_run_at')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
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
                \Filament\Actions\Action::make('run')
                    ->label('Test Rule')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->action(function (\App\Models\Rule $record): void {
                        \App\Jobs\GenerateContentJob::dispatch($record);
                        \Filament\Notifications\Notification::make()
                            ->title('Rule execution started!')
                            ->body('A GenerateContentJob has been dispatched. Please wait a few seconds and check the Drafts page.')
                            ->success()
                            ->send();
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
