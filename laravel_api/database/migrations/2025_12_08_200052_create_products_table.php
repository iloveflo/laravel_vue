<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // Khóa ngoại liên kết với bảng categories
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');

            $table->string('name', 150);
            $table->string('slug', 150)->unique();
            $table->text('description')->nullable();

            $table->decimal('price', 10, 2);
            $table->decimal('cost_price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();

            $table->unsignedInteger('quantity')->default(0);
            $table->string('sku', 50)->unique(); // Mã kho

            $table->enum('status', ['active', 'inactive', 'out_of_stock'])->default('active');
            $table->boolean('featured')->default(false); // Sản phẩm nổi bật
            $table->unsignedInteger('view_count')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
