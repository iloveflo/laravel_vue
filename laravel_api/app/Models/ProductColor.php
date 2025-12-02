<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductColor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'product_colors';

    /**
     * Các trường được phép fill.
     */
    protected $fillable = [
        'product_id',
        'color_name',
        'color_code',
        'quantity',
    ];

    /**
     * Các trường datetime
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Quan hệ: Màu sắc thuộc sản phẩm
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
