<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ProductVariant;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';

    /**
     * Các trường được phép fill.
     */
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'cost_price',
        'sale_price',
        'quantity',
        'sku',
        'status',
        'featured',
        'view_count',
    ];

    /**
     * Các trường datetime
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'featured'   => 'boolean',
    ];

    /**
     * Quan hệ: Sản phẩm thuộc danh mục
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Quan hệ: Sản phẩm có nhiều hình ảnh
     */
    // Sản phẩm có nhiều ảnh

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    // Ảnh chính
    public function mainImage()
    {
        return $this->hasOne(ProductImage::class, 'product_id')
            ->where('is_primary', 1)
            ->orderBy('sort_order');
    }
    // Accessor: trả về URL ảnh chính (hoặc null nếu không có)
    public function getMainImageUrlAttribute()
    {
        // ưu tiên ảnh đánh dấu is_primary
        $image = $this->mainImage ?: $this->images()->orderBy('sort_order')->first();

        if (!$image) {
            return null;
        }

        // image_path lưu kiểu: products/abc.jpg
        return asset('storage/' . $image->image_path);
    }
    /**
     * Quan hệ: Sản phẩm có nhiều size
     */


    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }


    /**
     * Quan hệ: Sản phẩm có nhiều đánh giá
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'product_id');
    }

    /**
     * Quan hệ: Sản phẩm có nhiều order_items
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }

    /**
     * Quan hệ: Sản phẩm có nhiều cart_sessions
     */
    public function cartSessions()
    {
        return $this->hasMany(CartSession::class, 'product_id');
    }
}
