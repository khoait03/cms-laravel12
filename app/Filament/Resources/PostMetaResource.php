<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostMetaResource\Pages;
use App\Models\PostMeta;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PostMetaResource extends Resource
{
    protected static ?string $model = PostMeta::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    
    protected static ?string $navigationLabel = 'Post Meta';
    
    protected static ?string $pluralModelLabel = 'Post Meta';
    
    protected static ?string $modelLabel = 'Post Meta';
    
    protected static ?string $navigationGroup = 'Blog Management';
    
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('post_id')
                    ->label('Bài viết')
                    ->relationship('post', 'title')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('title')
                            ->label('Tiêu đề')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('content')
                            ->label('Nội dung')
                            ->required()
                            ->rows(4),
                    ])
                    ->columnSpanFull(),
                    
                Forms\Components\TextInput::make('meta_key')
                    ->label('Meta Key')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ví dụ: custom_field, author_note, etc.')
                    ->helperText('Khóa để định danh metadata này'),
                    
                Forms\Components\Textarea::make('meta_value')
                    ->label('Meta Value')
                    ->rows(4)
                    ->placeholder('Giá trị của metadata')
                    ->helperText('Nội dung của metadata')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('post.title')
                    ->label('Bài viết')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 40) {
                            return null;
                        }
                        return $state;
                    }),
                    
                Tables\Columns\TextColumn::make('meta_key')
                    ->label('Meta Key')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                    
                Tables\Columns\TextColumn::make('meta_value')
                    ->label('Meta Value')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->wrap(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tạo lúc')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('post_id')
                    ->label('Bài viết')
                    ->relationship('post', 'title')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\Filter::make('meta_key')
                    ->form([
                        Forms\Components\TextInput::make('meta_key')
                            ->label('Meta Key')
                            ->placeholder('Nhập meta key để lọc'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['meta_key'],
                                fn (Builder $query, $metaKey): Builder => $query->where('meta_key', 'like', "%{$metaKey}%"),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPostMetas::route('/'),
            'create' => Pages\CreatePostMeta::route('/create'),
            // 'view' => Pages\ViewPostMeta::route('/{record}'),
            'edit' => Pages\EditPostMeta::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    
    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }
}