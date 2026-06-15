<?php

namespace App\Jobs;

use App\Models\TelegramAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncTelegramProfileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $accountId;
    public $data;

    /**
     * Create a new job instance.
     */
    public function __construct($accountId, $data)
    {
        $this->accountId = $accountId;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $record = TelegramAccount::find($this->accountId);
        if (!$record || $record->login_status !== 'logged_in') {
            return;
        }

        $sessionPath = storage_path('app/madeline/session_' . $record->id . '.madeline');
        
        try {
            if (file_exists($sessionPath)) {
                $settings = new \danog\MadelineProto\Settings();
                $settings->setAppInfo(
                    (new \danog\MadelineProto\Settings\AppInfo())
                        ->setApiId((int) \App\Models\Setting::get('telegram_api_id', env('TELEGRAM_API_ID', 2040)))
                        ->setApiHash(\App\Models\Setting::get('telegram_api_hash', env('TELEGRAM_API_HASH', 'b18441a1ff607e10a989891a5462e627')))
                );
                
                $MadelineProto = new \danog\MadelineProto\API($sessionPath, $settings);
                
                // Update Name
                $MadelineProto->account->updateProfile([
                    'first_name' => $this->data['first_name'] ?? '',
                    'last_name' => $this->data['last_name'] ?? '',
                ]);

                // Update Photo
                if (!empty($this->data['profile_photo_path'])) {
                    $photoPath = storage_path('app/public/' . $this->data['profile_photo_path']);
                    if (file_exists($photoPath)) {
                        $MadelineProto->photos->uploadProfilePhoto(['file' => $photoPath]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to sync profile to Telegram: " . $e->getMessage());
        }
    }
}
