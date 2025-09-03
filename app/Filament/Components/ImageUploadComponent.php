<?php

namespace App\Filament\Components;

use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ImageUploadComponent
{

    /**
     * Tạo một component FileUpload cho xử lý upload hình ảnh với các tùy chọn cấu hình linh hoạt.
     *
     * @param string $name     Tên của trường dữ liệu trong form
     *
     * @param string $slug     Tiền tố slug chuẩn hóa, dùng để tạo tên file.
     *
     * @param string $default  Giá trị mặc định cho tên file nếu `$name` không có giá trị.
     *
     * @param string $directory Thư mục lưu trữ file trên ổ đĩa. Mặc định là `'images'`.
     *                         Ví dụ: `'images/founders'`, `'uploads/avatars'`.
     * @param string $label
     *
     * @return FileUpload
     *
     * @example
     * // Sử dụng hàm để tạo FileUpload cho hình ảnh đại diện người dùng:
     * ImageUploadComponent::make(
     *     name: 'user_avatar',
     *     slug: 'avatar',
     *     default: 'default-avatar',
     *     directory: 'images/avatars',
     *     label: 'Chọn ảnh đại diện'
     * );
     */
    public static function make(
        $name,
        $slug,
        $default,
        $directory = 'images',
        $label = 'Chọn ảnh định dạng png, jpg, jpeg'
    ): FileUpload
    {
        return FileUpload::make($name)
            ->label($label)
            ->image()
            ->disk('public')
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/svg'])
            ->directory($directory)
            ->reorderable()
            ->preserveFilenames()
            ->getUploadedFileNameForStorageUsing(
                function (TemporaryUploadedFile $file, $record, callable $get)
                use ($name, $slug, $default)
                {

                $title = $get($slug) ?? $default;

                $nameImage = Str::slug($title);
                return $nameImage . '-' . Str::random(5) . '.' . $file->getClientOriginalExtension();
            })
            // Xóa ảnh trong storage
            ->deleteUploadedFileUsing(function ($file) {
                if ($file instanceof TemporaryUploadedFile) {
                    Storage::disk('public')->delete($file->getPathname());
                } elseif (is_string($file)) {
                    Storage::disk('public')->delete($file);
                }
            });
    }



    public static function getStorageImageUrl ($path, $default)
    {
        // Kiểm tra nếu $path là URL
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // Kiểm tra ảnh trong thư mục storage
        if ($path && Storage::disk('public')->exists($path)) {
            return asset('storage/' . $path);
        }

        // Trả về ảnh mặc định
        return asset($default);
    }
}