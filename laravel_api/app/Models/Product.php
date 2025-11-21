<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    /**
     * Quan hệ: Sản phẩm có nhiều size
     */
    public function sizes()
    {
        return $this->hasMany(ProductSize::class, 'product_id');
    }

    /**
     * Quan hệ: Sản phẩm có nhiều màu
     */
    public function colors()
    {
        return $this->hasMany(ProductColor::class, 'product_id');
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
