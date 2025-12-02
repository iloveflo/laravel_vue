<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // API lấy sản phẩm theo category
    public function getByCategory(Request $request, $slug)
    {
        // Lấy category_id theo slug
        $category = Category::where('slug', $slug)->first();

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        // Query sản phẩm
        $query = Product::with(['images', 'sizes', 'colors'])
            ->where('category_id', $category->id)
            ->where('status', 'active') // hoặc 1 nếu status là int
            ->orderBy('created_at', 'desc');

        // Lọc theo khoảng giá
        if ($request->has('min_price') && $request->min_price !== null) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price') && $request->max_price !== null) {
            $query->where('price', '<=', $request->max_price);
        }

        // Lọc theo size
        if ($request->has('sizes') && $request->sizes) {
            $sizes = explode(',', $request->sizes);
            $query->whereHas('sizes', function($q) use ($sizes) {
                $q->whereIn('size', $sizes);
            });
        }

        // Lọc theo màu
        if ($request->has('colors') && $request->colors) {
            $colors = explode(',', $request->colors);
            $query->whereHas('colors', function($q) use ($colors) {
                $q->whereIn('color_name', $colors);
            });
        }

        // Phân trang
        $products = $query->paginate(12);

        return response()->json([
            'category' => $category,
            'products' => $products
        ]);
    }


    public function getAll(Request $request) 
    {
        $query = Product::with(['images', 'sizes', 'colors'])
            ->where('status', 'active') // status enum: active
            ->orderBy('created_at', 'desc');

        // Lọc theo khoảng giá
        if ($request->has('min_price') && $request->min_price !== null) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price') && $request->max_price !== null) {
            $query->where('price', '<=', $request->max_price);
        }

        // Lọc theo size (size là quan hệ ProductSize)
        if ($request->has('sizes') && $request->sizes) {
            $sizes = explode(',', $request->sizes);
            $query->whereHas('sizes', function($q) use ($sizes) {
                $q->whereIn('size', $sizes);
            });
        }

        // Lọc theo màu (color là quan hệ ProductColor)
        if ($request->has('colors') && $request->colors) {
            $colors = explode(',', $request->colors);
            $query->whereHas('colors', function($q) use ($colors) {
                $q->whereIn('color_name', $colors);
            });
        }

        // Phân trang
        $products = $query->paginate(20);

        return response()->json($products);
    }
}
