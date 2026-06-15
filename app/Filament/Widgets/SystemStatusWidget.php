<?php

namespace App\Filament\Widgets;

use App\Models\Draft;
use App\Models\PublishedPost;
use App\Models\ScheduledPost;
use App\Models\HeartbeatLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SystemStatusWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $stats = \Illuminate\Support\Facades\Cache::remember('system_status_widget_stats', 300, function () {
            $lastHeartbeat = HeartbeatLog::latest('ticked_at')->first();
            $heartbeatStatus = $lastHeartbeat && $lastHeartbeat->ticked_at->diffInSeconds(now()) < 120 
                ? 'ALIVE' : 'OFFLINE';
            
            $heartbeatDesc = $lastHeartbeat ? 'Heartbeat ' . $lastHeartbeat->ticked_at->diffForHumans() : 'No heartbeat yet';
            
            $tokensUsedToday = Draft::whereDate('created_at', today())->sum('ai_tokens_used');
            $activeRules = \App\Models\Rule::where('is_active', true)->count();
            
            $pendingDrafts = Draft::whereIn('status', ['queued', 'ready'])->count();
            $publishedPosts = PublishedPost::count();
            $failedPosts = Draft::where('status', 'failed')->count() + ScheduledPost::where('status', 'failed')->count();

            return [
                'heartbeatStatus' => $heartbeatStatus,
                'heartbeatDesc' => $heartbeatDesc,
                'tokensUsedToday' => $tokensUsedToday,
                'activeRules' => $activeRules,
                'pendingDrafts' => $pendingDrafts,
                'publishedPosts' => $publishedPosts,
                'failedPosts' => $failedPosts,
            ];
        });

        return [
            Stat::make('SYSTEM STATUS', $stats['heartbeatStatus'])
                ->description($stats['heartbeatDesc'])
                ->descriptionIcon('heroicon-m-heart')
                ->color($stats['heartbeatStatus'] === 'ALIVE' ? 'success' : 'danger'),
            
            Stat::make('PENDING DRAFTS', $stats['pendingDrafts'])
                ->description('Ready to schedule')
                ->descriptionIcon('heroicon-m-document')
                ->color('info'),
                
            Stat::make('PUBLISHED POSTS', $stats['publishedPosts'])
                ->description('Total published')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            Stat::make('AI TOKENS', number_format((float)$stats['tokensUsedToday']))
                ->description('Tokens consumed today')
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color('primary'),
                
            Stat::make('ACTIVE RULES', $stats['activeRules'])
                ->description('Running tasks')
                ->descriptionIcon('heroicon-m-bolt')
                ->color('warning'),

            Stat::make('FAILED POSTS', $stats['failedPosts'])
                ->description('Needs attention')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger'),
        ];
    }
}
