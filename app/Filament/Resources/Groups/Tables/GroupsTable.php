<?php

namespace App\Filament\Resources\Groups\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Group / Group Title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('username')
                    ->label('Username / ID')
                    ->formatStateUsing(fn ($state, $record) => $state ? '@' . $state : $record->chat_id)
                    ->searchable()
                    ->copyable(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'group' => 'success',
                        'supergroup' => 'warning',
                        'group' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('account.first_name')
                    ->label('Connected Account')
                    ->formatStateUsing(fn ($record) => $record->account?->full_name ?? 'N/A')
                    ->icon('heroicon-o-user')
                    ->sortable(),
                IconColumn::make('is_active')
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
                \Filament\Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
