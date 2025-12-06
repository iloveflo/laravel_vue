<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
   public function getByCategory(Request $request, $slug)
    {
        // 1. Tìm Category và load sẵn relationship children để tối ưu query
        $category = Category::with('children')->where('slug', $slug)->first();

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        // 2. Xác định danh sách ID (Logic chuẩn: lấy chính nó + con của nó)
        // Nếu là cha -> lấy ID của nó + ID các con
        // Nếu là con -> children rỗng -> chỉ lấy ID của nó
        $targetIds = $category->children->pluck('id')->toArray();
        $targetIds[] = $category->id; // Luôn thêm chính ID hiện tại vào

        // 3. Query sản phẩm
        $query = Product::with(['images', 'sizes', 'colors']) // Eager loading
            ->whereIn('category_id', $targetIds)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc');

        // --- CÁC BỘ LỌC ---
        
        // Lọc giá
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float)$request->min_price);
        }
        
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float)$request->max_price);
        }

        // Lọc Size (Dùng whereHas là đúng)
        if ($request->filled('sizes')) {
            $sizes = explode(',', $request->sizes);
            $query->whereHas('sizes', function($q) use ($sizes) {
                $q->whereIn('size', $sizes);
            });
        }

        // Lọc Màu
        if ($request->filled('colors')) {
            $colors = explode(',', $request->colors);
            $query->whereHas('colors', function($q) use ($colors) {
                // Lưu ý: Cần check đúng tên cột trong bảng colors (color_name hay name?)
                $q->whereIn('color_name', $colors); 
            });
        }

        // 4. Phân trang
        $perPage = $request->input('per_page', 12); // Mặc định 20
        if ($perPage > 20) $perPage = 12; // Nếu xin > 12 thì ép về 20
        $products = $query->paginate($perPage);

        // 5. Trả về response
        return response()->json([
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug
            ],
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
        $products = $query->paginate(12);

        return response()->json($products);
    }
}
