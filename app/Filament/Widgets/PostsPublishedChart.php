<?php

namespace App\Filament\Widgets;

use App\Models\PublishedPost;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class PostsPublishedChart extends ChartWidget
{
    protected ?string $heading = 'Posts Published - Last 7 Days';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];
    protected static bool $isLazy = false;

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('M d');
            
            $data[] = PublishedPost::whereDate('published_at', $date->toDateString())->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Posts Published',
                    'data' => $data,
                    'fill' => 'start',
                    'backgroundColor' => '#eff6ff',
                    'borderColor' => '#3b82f6',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
