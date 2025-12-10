<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\ChatbotConversation;
use App\Models\Product;
use App\Models\Coupon;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        // 1. Xác thực
        $request->validate([
            'message' => 'required|string|max:1000',
            'session_id' => 'nullable|string',
        ]);

        $userMessage = $request->input('message');
        $sessionId = $request->input('session_id') ?? (string) Str::uuid();
        $userId = auth()->id() ?? null;

        // 2. Lưu tin nhắn User
        $conversation = ChatbotConversation::create([
            'session_id' => $sessionId,
            'user_id'    => $userId,
            'message'    => $userMessage,
            'response'   => null,
        ]);

        try {
            // --- TÌM KIẾM CHÍNH XÁC (Logic mới) ---
            $products = $this->searchProducts($userMessage);

            // --- LẤY COUPON ---
            $coupons = $this->getActiveCoupons();

            // 4. Context
            $productContext = $this->formatProductsToString($products);
            $couponContext = $this->formatCouponsToString($coupons);

            // 5. Prompt
            $systemPrompt = "Bạn là chuyên gia bán hàng AI. Dựa trên dữ liệu kho hàng thực tế bên dưới:\n\n" .
                "--- SẢN PHẨM TÌM THẤY ---\n" .
                ($productContext ?: "Không tìm thấy sản phẩm khớp yêu cầu.") . "\n\n" .
                "--- KHUYẾN MÃI ---\n" .
                ($couponContext ?: "Không có mã giảm giá.") . "\n\n" .
                "YÊU CẦU: \n" .
                "- Mời khách xem sản phẩm nếu có (nhấn mạnh màu/size khách cần).\n" .
                "- Gợi ý mã giảm giá phù hợp để chốt đơn.\n" .
                "- Trả lời ngắn gọn, tự nhiên.";

            // 6. Gọi API
            $apiKey = env('GEMINI_API_KEY');
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [[
                        'parts' => [['text' => $systemPrompt . "\n\nKhách: " . $userMessage]]
                    ]]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $aiReply = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Xin lỗi, tôi chưa hiểu ý bạn.';
            } else {
                Log::error('Gemini API Error: ' . $response->body());
                $aiReply = 'Hệ thống đang bận.';
            }

        } catch (\Exception $e) {
            Log::error('Chatbot Error: ' . $e->getMessage());
            $aiReply = 'Lỗi hệ thống.';
            $products = [];
        }

        // 8. Lưu DB
        $productsJson = (!empty($products) && count($products) > 0) ? json_encode($products) : null;
        $conversation->update([
            'response' => $aiReply,
            'products_json' => $productsJson
        ]);

        return response()->json([
            'reply'      => $aiReply,
            'products'   => $products,
            'session_id' => $sessionId
        ]);
    }

    /**
     * CẢI TIẾN LỚN NHẤT: Logic tìm kiếm 2 lớp (Strict Mode -> Broad Mode)
     */
    private function searchProducts($message)
    {
        // 1. Tách và lọc từ khóa rác
        $rawKeywords = explode(' ', $message);
        $keywords = [];
        // Danh sách từ nối tiếng Việt cần bỏ qua để query chính xác hơn
        $stopWords = ['là', 'của', 'có', 'không', 'những', 'các', 'cái', 'shop', 'cho', 'mình', 'em', 'anh', 'chị', 'muốn', 'mua', 'tìm', 'giá', 'bao', 'nhiêu'];
        
        foreach ($rawKeywords as $word) {
            $word = strtolower(trim($word));
            if (strlen($word) >= 2 && !in_array($word, $stopWords)) {
                $keywords[] = $word;
            }
        }

        if (empty($keywords)) {
            // Nếu lọc xong mà không còn từ nào (vd khách chỉ chat "alo"), trả về sp mới nhất
            return $this->getFallbackProducts();
        }

        // --- LỚP 1: TÌM KIẾM CHẶT CHẼ (AND Logic) ---
        // Sản phẩm phải thỏa mãn TẤT CẢ từ khóa
        // Ví dụ: "Áo đỏ" -> Phải có (Áo) VÀ (Đỏ)
        $query = Product::with(['category', 'variants'])->where('status', 'active');

        foreach ($keywords as $word) {
            $query->where(function ($subQ) use ($word) {
                // Từ khóa này phải xuất hiện ở ÍT NHẤT MỘT TRONG CÁC CỘT SAU:
                $subQ->orWhere('name', 'like', "%{$word}%")
                     ->orWhere('description', 'like', "%{$word}%")
                     ->orWhereHas('category', function($catQ) use ($word) {
                         $catQ->where('name', 'like', "%{$word}%");
                     })
                     ->orWhereHas('variants', function($varQ) use ($word) {
                         $varQ->where('color_name', 'like', "%{$word}%")
                              ->orWhere('size', 'like', "%{$word}%");
                     });
            });
        }

        $results = $query->take(6)->get();

        // Nếu Lớp 1 tìm thấy kết quả, trả về ngay (Đây là kết quả chính xác nhất)
        if ($results->isNotEmpty()) {
            return $results;
        }

        // --- LỚP 2: TÌM KIẾM RỘNG (OR Logic - Fallback) ---
        // Nếu Lớp 1 không ra gì (do khách gõ sai hoặc tìm quá khó), chuyển sang tìm "Có từ nào hay từ đó"
        $queryBroad = Product::with(['category', 'variants'])->where('status', 'active');
        
        $queryBroad->where(function ($subQ) use ($keywords) {
            foreach ($keywords as $word) {
                $subQ->orWhere('name', 'like', "%{$word}%")
                     ->orWhere('description', 'like', "%{$word}%")
                     ->orWhereHas('category', function($catQ) use ($word) {
                         $catQ->where('name', 'like', "%{$word}%");
                     })
                     ->orWhereHas('variants', function($varQ) use ($word) {
                         $varQ->where('color_name', 'like', "%{$word}%")
                              ->orWhere('size', 'like', "%{$word}%");
                     });
            }
        });

        $broadResults = $queryBroad->take(6)->get();

        if ($broadResults->isNotEmpty()) {
            return $broadResults;
        }

        // --- LỚP 3: KHÔNG TÌM THẤY GÌ ---
        // Trả về sản phẩm mới nhất để gợi ý
        return $this->getFallbackProducts();
    }

    private function getFallbackProducts() {
        return Product::with(['category', 'variants'])
            ->where('status', 'active')
            ->latest()
            ->take(3)
            ->get();
    }

    private function getActiveCoupons()
    {
        return Coupon::where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->whereColumn('used_count', '<', 'usage_limit')
            ->orderBy('discount_value', 'desc')
            ->take(3)
            ->get();
    }

    private function formatProductsToString($products)
    {
        if ($products->isEmpty()) return "";
        $context = "";
        foreach ($products as $p) {
            $price = number_format($p->sale_price > 0 ? $p->sale_price : $p->price);
            $variants = $p->variants->map(function($v) {
                return "{$v->color_name}-Size{$v->size}";
            })->unique()->implode(', ');
            
            $context .= "- {$p->name} ({$price}đ). Có: {$variants}\n";
        }
        return $context;
    }

    private function formatCouponsToString($coupons)
    {
        if ($coupons->isEmpty()) return "";
        $context = "";
        foreach ($coupons as $c) {
            $val = $c->discount_type === 'percent' ? "{$c->discount_value}%" : number_format($c->discount_value) . "đ";
            $context .= "- Mã {$c->code}: Giảm {$val} (Đơn từ " . number_format($c->min_order_value) . "đ)\n";
        }
        return $context;
    }

    public function getHistory(Request $request)
    {
        // (Giữ nguyên code getHistory cũ của bạn)
        $sessionId = $request->input('session_id');
        if (!$sessionId) return response()->json([]);
        $conversations = ChatbotConversation::where('session_id', $sessionId)->orderBy('created_at', 'asc')->get();
        $history = [];
        foreach ($conversations as $conv) {
            $history[] = ['sender' => 'user', 'text' => $conv->message, 'products' => []];
            if ($conv->response) {
                $savedProducts = $conv->products_json ? json_decode($conv->products_json, true) : [];
                $history[] = ['sender' => 'bot', 'text' => $conv->response, 'products' => $savedProducts];
            }
        }
        return response()->json($history);
    }
}