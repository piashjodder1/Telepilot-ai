<?php

namespace App\Services;

use App\Models\Draft;
use App\Models\Rule;
use App\Models\ScheduledPost;
use App\Models\HeartbeatLog;
use App\Jobs\GenerateContentJob;
use App\Jobs\PublishPostJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HeartbeatService
{
    public function tick(): void
    {
        // Emergency stop চেক
        if (Cache::get('autopulse_emergency_stop', false)) {
            Log::warning('AutoPulse: Emergency stop active. Skipping heartbeat.');
            return;
        }

        $startTime     = microtime(true);
        $rulesChecked  = 0;
        $jobsDispatched = 0;
        $postsPublished = 0;
        $errors        = 0;

        try {
            // ── Step 1: Active Rules যেগুলোর next_run_at এসে গেছে ──
            $activeRules = Rule::where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('next_run_at')
                          ->orWhere('next_run_at', '<=', now());
                })
                ->get();

            foreach ($activeRules as $rule) {
                $rulesChecked++;

                try {
                    // ── Time Window চেক (active_from / active_until) ──
                    if (!$this->isWithinTimeWindow($rule)) {
                        continue;
                    }

                    // ── Days Active চেক ──
                    if (!$this->isActiveDay($rule)) {
                        continue;
                    }

                    // ── Max Per Day চেক ──
                    if ($this->hasReachedDailyLimit($rule)) {
                        continue;
                    }

                    // ── Job Dispatch করো ──
                    GenerateContentJob::dispatch($rule);
                    $jobsDispatched++;

                    // ── next_run_at আপডেট করো ──
                    $rule->update([
                        'next_run_at' => $this->calcNextRun($rule),
                        'last_run_at' => now(),
                    ]);

                } catch (\Exception $e) {
                    $errors++;
                    Log::error("Heartbeat rule error (Rule ID: {$rule->id}): " . $e->getMessage());
                }
            }

            // ── Step 2: Due ScheduledPosts publish করো ──
            $duePosts = ScheduledPost::where('status', 'scheduled')
                ->where('scheduled_at', '<=', now())
                ->get();

            foreach ($duePosts as $post) {
                try {
                    PublishPostJob::dispatch($post);
                    $postsPublished++;
                } catch (\Exception $e) {
                    $errors++;
                    Log::error("Heartbeat publish error (Post ID: {$post->id}): " . $e->getMessage());
                }
            }

        } catch (\Exception $e) {
            $errors++;
            Log::error('Heartbeat fatal error: ' . $e->getMessage());
        }

        // ── Heartbeat Log সেভ করো ──
        HeartbeatLog::create([
            'ticked_at'      => now(),
            'rules_checked'  => $rulesChecked,
            'jobs_dispatched' => $jobsDispatched,
            'posts_published' => $postsPublished,
            'errors'         => $errors,
            'duration_ms'    => (int) ((microtime(true) - $startTime) * 1000),
        ]);
    }

    /**
     * বর্তমান সময় Rule-এর active_from থেকে active_until-এর মধ্যে আছে কিনা চেক করো
     */
    private function isWithinTimeWindow(Rule $rule): bool
    {
        if (!$rule->active_from || !$rule->active_until) {
            return true; // কোনো time window না থাকলে সবসময় active
        }

        $timezone   = $rule->timezone ?? 'Asia/Dhaka';
        $now        = now()->setTimezone($timezone);
        $activeFrom = Carbon::createFromTimeString($rule->active_from instanceof \Carbon\Carbon ? $rule->active_from->format('H:i:s') : $rule->active_from, $timezone);
        $activeUntil = Carbon::createFromTimeString($rule->active_until instanceof \Carbon\Carbon ? $rule->active_until->format('H:i:s') : $rule->active_until, $timezone);

        $activeFrom  = $now->copy()->setTimeFrom($activeFrom);
        $activeUntil = $now->copy()->setTimeFrom($activeUntil);

        if ($activeFrom->greaterThan($activeUntil)) {
            // Time window crosses midnight (e.g., 05:00 PM to 06:00 AM)
            return clone $now >= clone $activeFrom || clone $now <= clone $activeUntil;
        }

        // Normal time window (e.g., 09:00 AM to 11:00 PM)
        return clone $now >= clone $activeFrom && clone $now <= clone $activeUntil;
    }

    /**
     * আজকে Rule-এর জন্য active day আছে কিনা চেক করো
     */
    private function isActiveDay(Rule $rule): bool
    {
        if (empty($rule->days_active)) {
            return true; // দিন সেট না থাকলে সবসময় active
        }

        $timezone   = $rule->timezone ?? 'Asia/Dhaka';
        $dayOfWeek  = strtolower(now()->setTimezone($timezone)->format('D')); // mon, tue, wed...

        $daysActive = is_array($rule->days_active) ? $rule->days_active : explode(',', $rule->days_active);

        return in_array($dayOfWeek, array_map('strtolower', $daysActive));
    }

    /**
     * আজকে এই Rule আর পোস্ট করতে পারবে কিনা চেক করো (max_per_day)
     */
    private function hasReachedDailyLimit(Rule $rule): bool
    {
        if (empty($rule->max_per_day)) {
            return false;
        }

        // Use Cache to prevent race conditions when multiple jobs are dispatched quickly
        $cacheKey = "rule_{$rule->id}_daily_count_" . today()->format('Y-m-d');
        
        $todayCount = Cache::get($cacheKey, 0);

        if ($todayCount >= $rule->max_per_day) {
            return true;
        }

        // Increment the cache count safely
        Cache::increment($cacheKey);
        // Ensure the cache expires at the end of the day
        Cache::put($cacheKey, Cache::get($cacheKey), now()->endOfDay());

        return false;
    }

    /**
     * Rule-এর frequency অনুযায়ী next_run_at calculate করো
     */
    private function calcNextRun(Rule $rule): Carbon
    {
        return match($rule->frequency) {
            'every_1_hour'   => now()->addHour(),
            'every_3_hours'  => now()->addHours(3),
            'every_6_hours'  => now()->addHours(6),
            'every_12_hours' => now()->addHours(12),
            'twice_daily'    => now()->addHours(12),
            'once_daily',
            'daily'          => now()->addDay(),
            'custom_minutes' => now()->addMinutes(max(1, (int) $rule->custom_minutes)),
            default          => now()->addHours(6),
        };
    }
}
