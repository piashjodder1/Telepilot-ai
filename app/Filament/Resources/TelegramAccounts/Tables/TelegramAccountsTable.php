<?php

namespace App\Filament\Resources\TelegramAccounts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Log;
use danog\MadelineProto\API;
use danog\MadelineProto\Settings;
use danog\MadelineProto\Settings\AppInfo;
use Exception;

class TelegramAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\ImageColumn::make('profile_photo_path')
                    ->label('Profile')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->first_name ?? 'User') . '&color=FFFFFF&background=0284c7'),
                TextColumn::make('full_name')
                    ->label('Name')
                    ->description(fn ($record) => $record->username ? '@' . $record->username : null)
                    ->searchable(['first_name', 'last_name']),
                TextColumn::make('phone_number')
                    ->label('Number')
                    ->searchable(),
                TextColumn::make('login_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'logged_in' => 'success',
                        'awaiting_code' => 'warning',
                        'awaiting_password' => 'warning',
                        'logged_out' => 'danger',
                        default => 'gray',
                    }),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                TextColumn::make('last_used_at')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([
                Action::make('login')
                    ->label('Login / Authenticate')
                    ->icon('heroicon-o-key')
                    ->color('primary')
                    ->modalContent(fn ($record) => view('telegram-login-modal', ['recordId' => $record->id]))
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->modalWidth('md')
                    ->visible(fn ($record) => $record->login_status !== 'logged_in'),

                
                Action::make('logout')
                    ->label('Logout')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $sessionPath = storage_path('app/madeline/session_' . $record->id . '.madeline');
                        if (file_exists($sessionPath)) {
                            try {
                                $MadelineProto = new API($sessionPath);
                                $MadelineProto->logout();
                            } catch (Exception $e) {}
                            if (is_dir($sessionPath)) {
                                \Illuminate\Support\Facades\File::deleteDirectory($sessionPath);
                            } else {
                                unlink($sessionPath);
                            }
                        }
                        $record->update(['login_status' => 'logged_out']);
                    })
                    ->visible(fn ($record) => $record->login_status === 'logged_in'),

                Action::make('edit_profile')
                    ->label('Edit')
                    ->icon('heroicon-m-pencil-square')
                    ->color('warning')
                    ->modalHeading('Edit Telegram Profile')
                    ->form([
                        TextInput::make('first_name')->label('First Name')->required()->maxLength(255),
                        TextInput::make('last_name')->label('Last Name')->maxLength(255),
                        \Filament\Forms\Components\FileUpload::make('profile_photo_path')
                            ->label('Profile Photo')
                            ->image()
                            ->directory('telegram_profiles')
                            ->visibility('public'),
                    ])
                    ->action(function ($record, array $data) {
                        // Save to database first
                        $record->update($data);

                        // Dispatch background job to sync with Telegram
                        if ($record->login_status === 'logged_in') {
                            \App\Jobs\SyncTelegramProfileJob::dispatch($record->id, $data);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Profile updated successfully')
                                ->body('The changes are being synced to Telegram in the background.')
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Profile updated locally')
                                ->body('Log in to sync changes to Telegram.')
                                ->success()
                                ->send();
                        }
                    }),

                Action::make('delete_account')
                    ->label('Delete')
                    ->icon('heroicon-m-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn($record) => $record->delete()),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
