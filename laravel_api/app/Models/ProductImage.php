<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductImage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'product_images';

    /**
     * Các trường được phép fill.
     */
    protected $fillable = [
        'product_id',
        'image_path',
        'is_primary',
        'sort_order',
    ];

    /**
     * Các trường datetime
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_primary' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    /**
     * Quan hệ: Ảnh thuộc sản phẩm
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
