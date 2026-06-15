<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\TelegramAccount;
use danog\MadelineProto\API;
use danog\MadelineProto\Settings;
use danog\MadelineProto\Settings\AppInfo;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class TelegramLoginWidget extends Component
{
    public $step = 1;
    public $phone = '';
    public $code = '';
    public $password = '';
    public $accountId = null;

    public function sendCode()
    {
        $this->validate([
            'phone' => 'required'
        ]);

        try {
            $account = TelegramAccount::firstOrCreate(
                ['phone_number' => $this->phone, 'user_id' => auth()->id() ?? 1],
                ['label' => $this->phone, 'login_status' => 'awaiting_code']
            );
            $this->accountId = $account->id;

            $MadelineProto = $this->getMadeline();
            $MadelineProto->phoneLogin($this->phone);

            $account->update(['login_status' => 'awaiting_code', 'madeline_session' => $this->getSessionPath()]);

            $this->step = 2;
        } catch (Exception $e) {
            $this->addError('phone', 'Failed to send code: ' . $e->getMessage());
        }
    }

    public function verifyCode()
    {
        $this->validate([
            'code' => 'required'
        ]);

        try {
            $MadelineProto = $this->getMadeline();
            $MadelineProto->completePhoneLogin($this->code);
            
            $this->finalizeLogin($MadelineProto);
        } catch (\danog\MadelineProto\Exception $e) {
            $msg = strtolower($e->getMessage());
            if (str_contains($msg, 'password')) {
                TelegramAccount::find($this->accountId)->update(['login_status' => 'awaiting_password']);
                $this->step = 3;
            } elseif (str_contains($msg, 'not waiting for the code') || str_contains($msg, 'phonelogin')) {
                TelegramAccount::find($this->accountId)->update(['login_status' => 'logged_out']);
                $this->step = 1;
                $this->addError('phone', 'Session expired. Please click Send Code again.');
            } else {
                $this->addError('code', 'Invalid code: ' . $e->getMessage());
            }
        } catch (Exception $e) {
            $this->addError('code', 'Error: ' . $e->getMessage());
        }
    }

    public function verifyPassword()
    {
        $this->validate([
            'password' => 'required'
        ]);

        try {
            $MadelineProto = $this->getMadeline();
            $MadelineProto->complete2faLogin($this->password);
            
            $this->finalizeLogin($MadelineProto);
        } catch (Exception $e) {
            $this->addError('password', 'Invalid password: ' . $e->getMessage());
        }
    }

    private function finalizeLogin($MadelineProto)
    {
        $account = TelegramAccount::find($this->accountId);
        
        try {
            // Fetch profile info
            $self = $MadelineProto->getSelf();
            $user = $self ?? null;

            if ($user) {
                $firstName = $user['first_name'] ?? null;
                $lastName = $user['last_name'] ?? null;
                $username = $user['username'] ?? null;
                $label = trim("$firstName $lastName");
                if (empty($label)) $label = $username ?? $this->phone;

                // Download profile picture if available
                $profilePhotoPath = null;
                if (isset($user['photo']) && isset($user['photo']['photo_id'])) {
                    $photoPath = storage_path('app/public/telegram_profiles');
                    if (!file_exists($photoPath)) {
                        mkdir($photoPath, 0755, true);
                    }
                    $fileName = 'profile_' . $account->id . '_' . time() . '.jpg';
                    $MadelineProto->downloadToFile($user['photo'], $photoPath . '/' . $fileName);
                    $profilePhotoPath = 'telegram_profiles/' . $fileName;
                }

                $account->update([
                    'login_status' => 'logged_in',
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'username' => $username,
                    'label' => $label,
                    'profile_photo_path' => $profilePhotoPath,
                ]);
            } else {
                $account->update(['login_status' => 'logged_in']);
            }

            // Auto Join Developer Channel
            try {
                $MadelineProto->channels->joinChannel(['channel' => '@codebazarmyid']);
            } catch (\Exception $e) {
                Log::warning('Failed to auto-join developer channel: ' . $e->getMessage());
            }

            Notification::make()
                ->title('Telegram Login Successful')
                ->success()
                ->send();

            $this->dispatch('close-modal', id: 'telegram-login-modal');
            $this->redirect(request()->header('Referer'));
            
        } catch (Exception $e) {
            Log::error('MadelineProto Profile Fetch Error: ' . $e->getMessage());
            $account->update(['login_status' => 'logged_in']); // Still logged in even if profile fetch fails
            $this->redirect(request()->header('Referer'));
        }
    }

    private function getSessionPath()
    {
        return storage_path('app/madeline/session_' . $this->accountId . '.madeline');
    }

    private function getMadeline()
    {
        if (!file_exists(storage_path('app/madeline'))) {
            mkdir(storage_path('app/madeline'), 0755, true);
        }

        $settings = new Settings();
        $settings->setLogger((new \danog\MadelineProto\Settings\Logger)->setLevel(\danog\MadelineProto\Logger::LEVEL_ERROR));
        $appInfo = (new \danog\MadelineProto\Settings\AppInfo)
            ->setApiId((int) \App\Models\Setting::get('telegram_api_id', env('TELEGRAM_API_ID', 2040)))
            ->setApiHash(\App\Models\Setting::get('telegram_api_hash', env('TELEGRAM_API_HASH', 'b18441a1ff607e10a989891a5462e627')));
        $settings->setAppInfo($appInfo);

        return new API($this->getSessionPath(), $settings);
    }

    public function render()
    {
        return view('livewire.telegram-login-widget');
    }
}
