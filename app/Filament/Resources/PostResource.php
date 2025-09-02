<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\KeyValue;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Bài viết';

    protected static ?string $modelLabel = 'Bài viết';

    protected static ?string $pluralModelLabel = 'Bài viết';

    protected static ?int $navigationSort = 1;

     protected static ?string $navigationGroup = 'Quản lý bài viết';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Nội dung')
                            ->icon('heroicon-m-document-text')
                            ->schema([
                                Section::make('Thông tin cơ bản')
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Tiêu đề')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (string $context, $state, Forms\Set $set) => 
                                                $context === 'create' ? $set('slug', Str::slug($state)) : null
                                            )
                                            ->maxLength(255),
                                        
                                        TextInput::make('slug')
                                            ->label('Slug')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->rules(['regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/']),

                                        Textarea::make('excerpt')
                                            ->label('Mô tả ngắn')
                                            ->rows(3)
                                            ->maxLength(500),

                                        RichEditor::make('content')
                                            ->label('Nội dung')
                                            ->required()
                                            ->toolbarButtons([
                                                'attachFiles',
                                                'blockquote',
                                                'bold',
                                                'bulletList',
                                                'codeBlock',
                                                'h2',
                                                'h3',
                                                'h4',
                                                'italic',
                                                'link',
                                                'orderedList',
                                                'redo',
                                                'strike',
                                                'table',
                                                'undo',
                                            ]),
                                    ])
                                    ->columns(2),

                                Section::make('Phân loại')
                                    ->schema([
                                        Select::make('categories')
                                            ->label('Danh mục')
                                            ->multiple()
                                            ->relationship('categories', 'name')
                                            ->preload()
                                            ->searchable()
                                            ->createOptionForm([
                                                TextInput::make('name')
                                                    ->label('Tên danh mục')
                                                    ->required(),
                                                TextInput::make('slug')
                                                    ->label('Slug'),
                                                Textarea::make('description')
                                                    ->label('Mô tả')
                                                    ->rows(2),
                                            ]),

                                        Select::make('tags')
                                            ->label('Thẻ')
                                            ->multiple()
                                            ->relationship('tags', 'name')
                                            ->preload()
                                            ->searchable()
                                            ->createOptionForm([
                                                TextInput::make('name')
                                                    ->label('Tên thẻ')
                                                    ->required(),
                                                TextInput::make('slug')
                                                    ->label('Slug'),
                                            ]),
                                    ])
                                    ->columns(2),
                            ]),

                        Tabs\Tab::make('Hình ảnh & Xuất bản')
                            ->icon('heroicon-m-photo')
                            ->schema([
                                Section::make('Hình ảnh đại diện')
                                    ->schema([
                                        FileUpload::make('thumbnail')
                                            ->label('Ảnh đại diện')
                                            ->image()
                                            ->directory('posts')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->imageEditorAspectRatios([
                                                '16:9',
                                                '4:3',
                                                '1:1',
                                            ])
                                            // ->getUploadedFileNameForStorageUsing(
                                            //     fn (TemporaryUploadedFile $file, Forms\Get $get): string => {
                                            //         $slug = $get('slug') ?: Str::slug($get('title') ?: 'untitled');
                                            //         $randomString = Str::random(5);
                                            //         $extension = $file->getClientOriginalExtension();
                                                    
                                            //         return $slug . '-' . $randomString . '.' . $extension;
                                            //     }
                                            // ),
                                    ]),

                                Section::make('Trạng thái xuất bản')
                                    ->schema([
                                        Toggle::make('is_published')
                                            ->label('Đã xuất bản')
                                            ->default(false)
                                            ->live(),

                                        DateTimePicker::make('published_at')
                                            ->label('Ngày xuất bản')
                                            ->visible(fn (Forms\Get $get) => $get('is_published'))
                                            ->default(now()),

                                        TextInput::make('views')
                                            ->label('Lượt xem')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled(),
                                    ])
                                    ->columns(2),
                            ]),

                        Tabs\Tab::make('SEO')
                            ->icon('heroicon-m-magnifying-glass')
                            ->schema([
                                Section::make('Tối ưu hóa SEO')
                                    ->schema([
                                        TextInput::make('seo_title')
                                            ->label('Tiêu đề SEO')
                                            ->maxLength(60)
                                            ->helperText('Tối đa 60 ký tự cho kết quả tối ưu'),

                                        Textarea::make('seo_description')
                                            ->label('Mô tả SEO')
                                            ->rows(3)
                                            ->maxLength(160)
                                            ->helperText('Tối đa 160 ký tự cho kết quả tối ưu'),

                                        TextInput::make('seo_keywords')
                                            ->label('Từ khóa SEO')
                                            ->helperText('Phân cách bằng dấu phẩy'),

                                        TextInput::make('canonical_url')
                                            ->label('URL Canonical')
                                            ->url()
                                            ->helperText('URL chính thức của bài viết'),
                                    ])
                                    ->columns(2),
                            ]),

                        Tabs\Tab::make('Dữ liệu mở rộng')
                            ->icon('heroicon-m-code-bracket')
                            ->schema([
                                Section::make('Schema JSON-LD')
                                    ->schema([
                                        KeyValue::make('schema_json')
                                            ->label('Schema JSON-LD')
                                            ->helperText('Dữ liệu có cấu trúc cho SEO'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('Ảnh')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder.png')),

                TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),

                TextColumn::make('categories.name')
                    ->label('Danh mục')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                TextColumn::make('tags.name')
                    ->label('Thẻ')
                    ->badge()
                    ->color('success')
                    ->limit(3)
                    ->searchable(),

                BooleanColumn::make('is_published')
                    ->label('Đã xuất bản')
                    ->sortable(),

                TextColumn::make('views')
                    ->label('Lượt xem')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('warning'),

                TextColumn::make('published_at')
                    ->label('Ngày xuất bản')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('categories')
                    ->label('Danh mục')
                    ->relationship('categories', 'name')
                    ->preload(),

                SelectFilter::make('tags')
                    ->label('Thẻ')
                    ->relationship('tags', 'name')
                    ->preload(),

                Filter::make('is_published')
                    ->label('Đã xuất bản')
                    ->query(fn (Builder $query): Builder => $query->where('is_published', true)),

                Filter::make('draft')
                    ->label('Bản nháp')
                    ->query(fn (Builder $query): Builder => $query->where('is_published', false)),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            // 'view' => Pages\ViewPost::route('/{record}'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}