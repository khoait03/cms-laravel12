<?php
// app/Filament/Widgets/CategoryStatsWidget.php

namespace App\Filament\Widgets;

use App\Models\Category;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class CategoryStatsWidget extends ChartWidget
{
    protected static ?string $heading = 'Phân bố bài viết theo danh mục';
    
    protected int | string | array $columnSpan = 1;
    
    protected static ?int $sort = 5;

    protected function getData(): array
    {
        $categories = Category::withCount('posts')
            ->having('posts_count', '>', 0)
            ->orderBy('posts_count', 'desc')
            ->take(6)
            ->get();

        return [
            'datasets' => [
                [
                    'data' => $categories->pluck('posts_count')->toArray(),
                    'backgroundColor' => [
                        '#3b82f6', // blue
                        '#ef4444', // red
                        '#10b981', // green
                        '#f59e0b', // yellow
                        '#8b5cf6', // purple
                        '#06b6d4', // cyan
                    ],
                ],
            ],
            'labels' => $categories->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
    
    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}