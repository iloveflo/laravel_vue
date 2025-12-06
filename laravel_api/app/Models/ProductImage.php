<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes; // Cân nhắc: Có nên dùng SoftDeletes không?

class ProductImage extends Model
{
    // Nếu bạn muốn xóa ảnh khỏi ổ cứng ngay lập tức trong Controller, 
    // Tốt nhất là KHÔNG dùng SoftDeletes để tránh logic mâu thuẫn.
    // Nếu vẫn muốn dùng SoftDeletes, hãy đọc phần lưu ý bên dưới.
    use HasFactory; 

    protected $table = 'product_images';

    protected $fillable = [
        'product_id',
        'image_path',
        'is_primary',
        'sort_order',
    ];

    // Tự động thêm field 'url' vào JSON trả về
    protected $appends = ['url'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Accessor: Sửa lại để trả về đúng đường dẫn
    public function getUrlAttribute()
    {
        if (!$this->image_path) {
            return null;
        }

        // SỬA: Bỏ 'public/' đi vì asset() đã trỏ vào thư mục public rồi
        // Controller lưu: uploads/products/abc.jpg
        // Kết quả: http://domain.com/uploads/products/abc.jpg
        return asset($this->image_path);
    }
}