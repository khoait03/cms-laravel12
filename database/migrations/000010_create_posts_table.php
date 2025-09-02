<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Tiêu đề
            $table->string('slug')->unique(); // Slug SEO
            $table->text('excerpt')->nullable(); // Mô tả ngắn
            $table->longText('content'); // Nội dung chính
            $table->string('thumbnail')->nullable(); // Ảnh đại diện
            $table->string('seo_title')->nullable(); // SEO title
            $table->string('seo_description')->nullable(); // SEO meta description
            $table->string('seo_keywords')->nullable(); // Từ khóa SEO
            $table->string('canonical_url')->nullable(); // Canonical
            $table->json('schema_json')->nullable(); // Schema JSON-LD
            $table->bigInteger('views')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Soft delete
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};