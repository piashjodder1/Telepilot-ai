<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

/**
 * cPanel Web Cron URL:
 * Shared hosting-এ এই URL cron job হিসেবে যোগ করুন:
 * wget -q -O - http://yourdomain.com/cron >/dev/null 2>&1
 *
 * অথবা সরাসরি php artisan schedule:run ব্যবহার করুন (recommended)
 */
Route::get('/cron', function () {
    set_time_limit(120);
    // Step 1: Heartbeat — Active rules চেক করো, jobs dispatch করো
    Artisan::call('autopulse:heartbeat');

    // Step 2: Queue থেকে job process করো (Shared Hosting Safe)
    // --stop-when-empty: Queue খালি হওয়া মাত্র প্রসেস শেষ হবে।
    // --max-time=50: 50 সেকেন্ডের বেশি চললে নিজে থেকেই স্টপ হয়ে যাবে, ফলে 508 Resource Limit বা Timeout হবে না।
    Artisan::call('queue:work', [
        '--stop-when-empty' => true,
        '--tries'           => 3,
        '--max-time'        => 50,
        '--rest'            => 1, // CPU যেন ফুল ইউজ না হয়
    ]);

    return response()->json([
        'status'  => 'success',
        'message' => 'Cron and Queues processed successfully.',
        'time'    => now()->timezone('Asia/Dhaka')->format('d M Y, h:i:s A'),
    ]);
});

// Telegram Webhook for Bot Approval
Route::post('/telegram/webhook', [\App\Http\Controllers\TelegramWebhookController::class, 'handle'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
