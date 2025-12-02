<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductSize extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'product_sizes';

    /**
     * Các trường được phép fill.
     */
    protected $fillable = [
        'product_id',
        'size',
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
     * Quan hệ: Size thuộc sản phẩm
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
