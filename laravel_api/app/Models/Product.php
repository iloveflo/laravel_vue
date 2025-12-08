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
        'sku',
        'status',
        'featured',
        'view_count',
    ];

    /**
     * Các trường datetime và cast
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'featured'   => 'boolean',
        // 'price' => 'decimal:2', // Nếu muốn ép kiểu trả về luôn là số thực
    ];

    /**
     * Tự động thêm attribute ảo vào JSON khi return response
     * Giúp frontend nhận được field 'stock_quantity' mà không cần gọi hàm
     */
    protected $appends = ['stock_quantity', 'main_image_url'];

    // ==========================================
    // ACCESSORS (Thuộc tính ảo)
    // ==========================================

    /**
     * Lấy tổng tồn kho từ các biến thể (Variants)
     * Gọi bằng: $product->stock_quantity
     */
    public function getStockQuantityAttribute()
    {
        // Nếu quan hệ variants đã được eager load (with('variants')), nó sẽ dùng luôn collection đó để tính
        // Nếu chưa, nó sẽ query database.
        return $this->variants->sum('quantity');
    }

    /**
     * Kiểm tra xem sản phẩm còn hàng không
     * Gọi bằng: $product->is_in_stock
     */
    public function getIsInStockAttribute()
    {
        return $this->stock_quantity > 0 && $this->status === 'active';
    }

    /**
     * Lấy URL ảnh chính
     * Gọi bằng: $product->main_image_url
     */
    public function getMainImageUrlAttribute()
    {
        // Ưu tiên lấy từ relation mainImage nếu đã load
        if ($this->relationLoaded('mainImage') && $this->mainImage) {
            return asset($this->mainImage->image_path);
        }

        // Nếu không, tìm trong relation images (lấy cái đầu tiên)
        $firstImage = $this->images->sortBy('sort_order')->first();
        
        if ($firstImage) {
            return asset($firstImage->image_path);
        }

        // Trả về ảnh placeholder mặc định nếu không có ảnh nào
        return asset('images/placeholder.png'); 
    }

    // ==========================================
    // RELATIONS (Quan hệ)
    // ==========================================

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    /**
     * Relationship lấy ra ảnh đại diện (để dùng Eager Loading: with('mainImage'))
     */
    public function mainImage()
    {
        return $this->hasOne(ProductImage::class, 'product_id')
            ->where('is_primary', 1)
            ->orderBy('sort_order');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'product_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }

    public function cartSessions()
    {
        return $this->hasMany(CartSession::class, 'product_id');
    }
}