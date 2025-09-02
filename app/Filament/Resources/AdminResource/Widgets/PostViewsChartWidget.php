<?php
// app/Filament/Widgets/PostViewsChartWidget.php

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PostViewsChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Lượt xem theo thời gian';
    
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 2;
    
    protected static ?string $pollingInterval = '1m';

    protected function getData(): array
    {
        // Lấy dữ liệu 7 ngày gần nhất
        $data = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo);
            $views = Post::whereDate('created_at', '<=', $date)
                        ->sum('views');
            
            return [
                'date' => $date->format('d/m'),
                'views' => $views,
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Tổng lượt xem',
                    'data' => $data->pluck('views')->toArray(),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $data->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return value.toLocaleString(); }',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
        ];
    }
}