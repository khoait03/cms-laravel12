<?php
// app/Filament/Widgets/BlogStatsWidget.php

namespace App\Filament\Widgets;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\PostMeta;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class BlogStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    
    // Refresh widget mỗi 30 giây
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // Thống kê cơ bản
        $totalPosts = Post::count();
        $publishedPosts = Post::published()->count();
        $draftPosts = Post::draft()->count();
        $totalCategories = Category::count();
        $totalTags = Tag::count();
        $totalViews = Post::sum('views');
        
        // Thống kê so sánh với tháng trước
        $currentMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();
        
        $postsThisMonth = Post::where('created_at', '>=', $currentMonth)->count();
        $postsLastMonth = Post::whereBetween('created_at', [$lastMonth, $currentMonth])->count();
        $postsTrend = $postsLastMonth > 0 ? (($postsThisMonth - $postsLastMonth) / $postsLastMonth) * 100 : 0;
        
        $viewsThisMonth = Post::where('created_at', '>=', $currentMonth)->sum('views');
        $viewsLastMonth = Post::whereBetween('created_at', [$lastMonth, $currentMonth])->sum('views');
        $viewsTrend = $viewsLastMonth > 0 ? (($viewsThisMonth - $viewsLastMonth) / $viewsLastMonth) * 100 : 0;

        return [
            Stat::make('Tổng bài viết', number_format($totalPosts))
                ->description($publishedPosts . ' đã xuất bản, ' . $draftPosts . ' nháp')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary')
                ->chart([7, 12, 8, 15, 20, 18, $totalPosts]),
                
            Stat::make('Bài viết tháng này', number_format($postsThisMonth))
                ->description(($postsTrend >= 0 ? '+' : '') . number_format($postsTrend, 1) . '% so với tháng trước')
                ->descriptionIcon($postsTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($postsTrend >= 0 ? 'success' : 'danger'),
                
            Stat::make('Tổng lượt xem', number_format($totalViews))
                ->description(($viewsTrend >= 0 ? '+' : '') . number_format($viewsTrend, 1) . '% so với tháng trước')
                ->descriptionIcon($viewsTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($viewsTrend >= 0 ? 'success' : 'warning')
                ->chart([100, 200, 150, 300, 400, 350, $totalViews]),
                
            Stat::make('Danh mục', number_format($totalCategories))
                ->description('Phân loại nội dung')
                ->descriptionIcon('heroicon-m-folder')
                ->color('info'),
                
            Stat::make('Thẻ tag', number_format($totalTags))
                ->description('Gắn thẻ bài viết')
                ->descriptionIcon('heroicon-m-hashtag')
                ->color('gray'),
                
            Stat::make('Tỷ lệ xuất bản', $totalPosts > 0 ? number_format(($publishedPosts / $totalPosts) * 100, 1) . '%' : '0%')
                ->description($publishedPosts . ' / ' . $totalPosts . ' bài viết')
                ->descriptionIcon('heroicon-m-eye')
                ->color($publishedPosts / max($totalPosts, 1) > 0.7 ? 'success' : 'warning'),
        ];
    }
}