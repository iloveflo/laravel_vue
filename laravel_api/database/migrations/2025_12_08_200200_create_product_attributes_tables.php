<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Bảng Size (Kích cỡ)
        Schema::create('product_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->enum('size', ['S', 'M', 'L', 'XL', 'XXL']);
            $table->unsignedInteger('quantity')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Bảng Color (Màu sắc)
        Schema::create('product_colors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('color_name', 50);
            $table->char('color_code', 7); // Mã màu Hex (VD: #FFFFFF)
            $table->unsignedInteger('quantity')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Bảng Images (Ảnh sản phẩm)
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('image_path');
            $table->boolean('is_primary')->default(false); // Ảnh đại diện
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Bảng Variants (Biến thể chi tiết: Màu + Size + SKU)
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('color_name', 50)->nullable();
            $table->char('color_code', 7)->nullable();
            $table->enum('size', ['S', 'M', 'L', 'XL', 'XXL']);
            $table->string('sku', 50)->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->decimal('additional_price', 10, 2)->default(0); // Giá cộng thêm nếu có
            $table->timestamps();
            // Bảng này trong dump cũ của bạn không có softDeletes
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('product_colors');
        Schema::dropIfExists('product_sizes');
    }
};
