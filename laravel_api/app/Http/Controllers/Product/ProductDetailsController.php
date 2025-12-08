<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductDetailsController extends Controller
{
    /**
     * GET /api/products/{slug}
     * Lấy chi tiết sản phẩm cho trang người dùng
     */
    public function show($slug)
    {
        // Lấy sản phẩm + quan hệ cần thiết
        $product = Product::with([
            'category:id,name,slug',

            // Ảnh: ảnh chính trước, sau đó theo sort_order
            'images' => function ($query) {
                $query->orderByDesc('is_primary')
                      ->orderBy('sort_order');
            },

            // Biến thể: màu + size + số lượng
            'variants',

            // Đánh giá + user của đánh giá
            'reviews' => function ($q) {
                $q->with([
                    // users table có full_name, không phải name
                    'user:id,full_name,avatar'
                ])->latest();
            },
        ])
        ->where('slug', $slug)
        ->where('status', 'active')   // chỉ lấy sản phẩm đang bán
        ->first();

        if (!$product) {
            return response()->json([
                'message' => 'Sản phẩm không tồn tại'
            ], 404);
        }

        // Ảnh chính: ưu tiên is_primary, nếu không có thì lấy ảnh đầu tiên
        $primaryImage = $product->images
            ->where('is_primary', 1)
            ->first() ?? $product->images->first();

        // Nhóm variants theo màu để frontend dễ hiển thị:
        // [
        //   {
        //     color_name: "Trắng",
        //     color_code: "#FFFFFF",
        //     variants: [
        //        { id, size: "M", quantity, sku, additional_price },
        //        { id, size: "L", quantity, sku, additional_price },
        //     ]
        //   },
        //   ...
        // ]
        $colorGroups = $product->variants
            ->groupBy(function ($v) {
                return $v->color_name ?: $v->color_code ?: 'default';
            })
            ->map(function ($group) {
                $first = $group->first();

                return [
                    'color_name' => $first->color_name,
                    'color_code' => $first->color_code,
                    'variants'   => $group->map(function ($v) {
                        return [
                            'id'               => $v->id,
                            'size'             => $v->size,
                            'quantity'         => $v->quantity,
                            'sku'              => $v->sku,
                            'additional_price' => $v->additional_price,
                        ];
                    })->values(),
                ];
            })
            ->values();

        return response()->json([
            'product'       => $product,
            'primary_image' => $primaryImage ? $primaryImage->url : null, // dùng accessor url trong ProductImage
            'images'        => $product->images,      // full list ảnh (có url)
            'variants'      => $product->variants,    // list biến thể thô
            'variants_by_color' => $colorGroups,      // đã group theo màu, tiện cho UI
            'reviews'       => $product->reviews,
        ]);
    }
}
