<?php
// app/Filament/Widgets/PopularPostsWidget.php

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PopularPostsWidget extends BaseWidget
{
    protected static ?string $heading = 'Bài viết phổ biến nhất';
    
    protected int | string | array $columnSpan = 1;
    
    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Post::query()
                    ->published()
                    ->orderBy('views', 'desc')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->limit(30)
                    ->weight('bold')
                    // ->url(fn (Post $record): string => route('filament.admin.resources.posts.view', $record))
                    ->color('primary'),
                    
                Tables\Columns\TextColumn::make('views')
                    ->label('Lượt xem')
                    ->numeric()
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn (int $state): string => number_format($state)),
                    
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Xuất bản')
                    ->date('d/m/Y')
                    ->color('gray'),
            ])
            ->paginated(false)
            ->emptyStateHeading('Chưa có bài viết nào')
            ->emptyStateIcon('heroicon-o-chart-bar');
    }
}