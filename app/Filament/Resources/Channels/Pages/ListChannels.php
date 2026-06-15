<?php

namespace App\Filament\Resources\Channels\Pages;

use App\Filament\Resources\Channels\ChannelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListChannels extends ListRecords
{
    protected static string $resource = ChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('sync_channels')
                ->label('Sync Channels')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->requiresConfirmation()
                ->action(function () {
                    $accounts = \App\Models\TelegramAccount::where('user_id', auth()->id())
                        ->where('login_status', 'logged_in')
                        ->get();
                        
                    $syncedCount = 0;
                    
                    foreach ($accounts as $account) {
                        try {
                            $sessionPath = storage_path('app/madeline/session_' . $account->id . '.madeline');
                            if (!file_exists($sessionPath)) continue;
                            
                            $settings = new \danog\MadelineProto\Settings();
                            $settings->setLogger((new \danog\MadelineProto\Settings\Logger)->setLevel(\danog\MadelineProto\Logger::LEVEL_ERROR));
                            
                            $MadelineProto = new \danog\MadelineProto\API($sessionPath, $settings);
                            
                            $dialogs = $MadelineProto->getFullDialogs();
                            
                            foreach ($dialogs as $dialog) {
                                try {
                                    $info = $MadelineProto->getInfo($dialog);
                                    if (isset($info['type']) && in_array($info['type'], ['channel', 'supergroup'])) {
                                        $chatId = $info['bot_api_id'] ?? $info['Chat']['id'] ?? null;
                                        if (!$chatId) continue;
                                        
                                        $isCreator = isset($info['Chat']['creator']) && $info['Chat']['creator'];
                                        $hasAdminRights = isset($info['Chat']['admin_rights']);
                                        
                                        if ($isCreator || $hasAdminRights) {
                                            \App\Models\Channel::updateOrCreate(
                                                [
                                                    'user_id' => auth()->id(),
                                                    'account_id' => $account->id,
                                                    'chat_id' => $chatId,
                                                ],
                                                [
                                                    'title' => $info['Chat']['title'] ?? 'Unknown',
                                                    'username' => $info['Chat']['username'] ?? null,
                                                    'type' => $info['type'],
                                                    'is_active' => true,
                                                ]
                                            );
                                            $syncedCount++;
                                        }
                                    }
                                } catch (\Exception $e) {
                                    // Ignore individual dialog errors
                                }
                            }
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('MadelineProto Sync Error: ' . $e->getMessage());
                            if (str_contains($e->getMessage(), 'SESSION_PASSWORD_NEEDED')) {
                                $account->update(['login_status' => 'awaiting_password']);
                            }
                        }
                    }
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Sync Complete')
                        ->body("Successfully synced {$syncedCount} channels/groups.")
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
