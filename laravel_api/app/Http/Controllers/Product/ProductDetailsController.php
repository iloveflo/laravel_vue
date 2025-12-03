<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductDetailsController extends Controller
{
    /**
     * Lấy chi tiết sản phẩm theo slug
     */
    public function show($slug)
    {
        // Lấy sản phẩm + toàn bộ quan hệ
        $product = Product::with([
            'category:id,name,slug',

            'images' => function ($query) {
                $query->orderByDesc('is_primary')
                      ->orderBy('sort_order');
            },

            'sizes:id,product_id,size,quantity',

            'colors:id,product_id,color_name,color_code,quantity',

            'reviews' => function ($q) {
                $q->with('user:id,name,avatar')->latest();
            },

        ])->where('slug', $slug)->first();

        if (!$product) {
            return response()->json([
                'message' => 'Sản phẩm không tồn tại'
            ], 404);
        }

        // Tách ảnh chính
        $primaryImage = $product->images->where('is_primary', 1)->first();

        return response()->json([
            'product'       => $product,
            'primary_image' => $primaryImage?->image_path ?? null,
            'images'        => $product->images,
            'sizes'         => $product->sizes,
            'colors'        => $product->colors,
            'reviews'       => $product->reviews,
        ]);
    }
}
