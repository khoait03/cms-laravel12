<?php
// app/Filament/Widgets/RecentPostsWidget.php

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentPostsWidget extends BaseWidget
{
    protected static ?string $heading = 'Bài viết mới nhất';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Post::query()
                    ->with(['categories'])
                    ->latest('created_at')
                    ->limit(8)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('Ảnh')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder.jpg')),
                    
                Tables\Columns\TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->weight('bold')
                    ->url(fn (Post $record): string => route('filament.admin.resources.posts.edit', $record))
                    ->color('primary'),
                    
                Tables\Columns\TextColumn::make('categories.name')
                    ->label('Danh mục')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Technology' => 'success',
                        'Web Development' => 'info',
                        'Mobile Development' => 'warning',
                        'Lifestyle' => 'danger',
                        default => 'gray',
                    })
                    ->separator(', ')
                    ->limit(2),
                    
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Trạng thái')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),
                    
                Tables\Columns\TextColumn::make('views')
                    ->label('Lượt xem')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 1000 => 'success',
                        $state >= 100 => 'warning',
                        default => 'gray',
                    }),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->color('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_published')
                    ->label('Trạng thái')
                    ->options([
                        1 => 'Đã xuất bản',
                        0 => 'Bản nháp',
                    ]),
            ])
            ->actions([

                Tables\Actions\ActionGroup::make([
                    
                    Tables\Actions\Action::make('view')
                        ->label('Xem')
                        ->icon('heroicon-o-eye')
                        ->color('info'),
                        // ->url(fn (Post $record): string => route('filament.admin.resources.posts.view', $record)),
                        
                    Tables\Actions\Action::make('edit')
                        ->label('Sửa')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning'),
                        // ->url(fn (Post $record): string => route('filament.admin.resources.posts.edit', $record)),
                        
                    Tables\Actions\Action::make('toggle_publish')
                        ->label(fn (Post $record): string => $record->is_published ? 'Ẩn' : 'Xuất bản')
                        ->icon(fn (Post $record): string => $record->is_published ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                        ->color(fn (Post $record): string => $record->is_published ? 'danger' : 'success')
                        ->action(function (Post $record): void {
                            $record->update([
                                'is_published' => !$record->is_published,
                                'published_at' => !$record->is_published ? now() : null,
                            ]);
                            
                            $this->dispatch('$refresh');
                        })
                        ->requiresConfirmation(),
                ])
            ])
            ->emptyStateHeading('Chưa có bài viết nào')
            ->emptyStateDescription('Tạo bài viết đầu tiên của bạn.')
            ->emptyStateIcon('heroicon-o-document-text')
            ->striped()
            ->paginated(false);
    }
}