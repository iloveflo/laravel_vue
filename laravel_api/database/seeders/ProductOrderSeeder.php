<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductOrderSeeder extends Seeder
{
    public function run()
    {
        $userIds = [3,4,5,8,9,10,11,12,13];

        /* ============================
         ========== PRODUCTS ==========
         ============================ */

        $products = [];

        for ($i = 1; $i <= 15; $i++) {

            $price = rand(150000, 800000);
            $cost = $price - rand(20000, 80000);

            $products[] = [
                'category_id' => rand(1, 5),
                'name' => "Sản phẩm mẫu $i",
                'slug' => "san-pham-mau-$i",
                'description' => "Mô tả sản phẩm mẫu số $i",
                'price' => $price,
                'cost_price' => $cost,
                'sale_price' => (rand(0, 1) ? $price - rand(20000, 100000) : null),
                'quantity' => rand(10, 100),
                'sku' => strtoupper(Str::random(10)),
                'status' => 'active',
                'featured' => rand(0, 1),
                'view_count' => rand(10, 200),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('products')->insert($products);


        /* ============================
         ======= PRODUCT IMAGES =======
         ============================ */

        $images = [];

        for ($id = 1; $id <= 15; $id++) {

            for ($img = 1; $img <= 3; $img++) {
                $images[] = [
                    'product_id' => $id,
                    'image_path' => "https://picsum.photos/seed/product{$id}_{$img}/600",
                    'is_primary' => $img == 1 ? 1 : 0,
                    'sort_order' => $img,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('product_images')->insert($images);


        /* ============================
         ======== PRODUCT SIZES ========
         ============================ */

        $sizes = ['S', 'M', 'L', 'XL', 'XXL'];
        $productSizes = [];

        for ($p = 1; $p <= 15; $p++) {
            foreach ($sizes as $s) {
                $productSizes[] = [
                    'product_id' => $p,
                    'size' => $s,
                    'quantity' => rand(5, 50),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('product_sizes')->insert($productSizes);


        /* ============================
         ======== PRODUCT COLORS =======
         ============================ */

        $colors = [
            ['Red', '#FF0000'],
            ['Black', '#000000'],
            ['White', '#FFFFFF'],
        ];

        $productColors = [];

        for ($p = 1; $p <= 15; $p++) {
            foreach ($colors as $c) {
                $productColors[] = [
                    'product_id' => $p,
                    'color_name' => $c[0],
                    'color_code' => $c[1],
                    'quantity' => rand(5, 40),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('product_colors')->insert($productColors);


        /* ============================
         ============ ORDERS ===========
         ============================ */

        $orders = [];

        for ($i = 1; $i <= 10; $i++) {

            $uid = $userIds[array_rand($userIds)];

            $orders[] = [
                'user_id' => $uid,
                'order_code' => strtoupper(Str::random(10)),
                'full_name' => "Khách hàng $uid",
                'email' => "user$uid@example.com",
                'phone' => '0123456789',
                'address' => 'Hà Nội, Việt Nam',
                'subtotal' => 0,
                'discount_amount' => 0,
                'shipping_fee' => 30000,
                'total_amount' => 0,
                'payment_method' => 'cod',
                'payment_status' => 'pending',
                'order_status' => 'pending',
                'note' => null,
                'session_id' => null,
                'tracking_token' => Str::uuid(),
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now(),
            ];
        }

        DB::table('orders')->insert($orders);


        /* ============================
         ========== ORDER ITEMS ========
         ============================ */

        $ordersList = DB::table('orders')->get();

        $orderItems = [];

        foreach ($ordersList as $order) {

            $numItems = rand(1, 4);
            $subtotal = 0;

            for ($i = 1; $i <= $numItems; $i++) {

                $productId = rand(1, 15);
                $product = DB::table('products')->where('id', $productId)->first();
                $image = DB::table('product_images')->where('product_id', $productId)->first();

                $qty = rand(1, 3);
                $lineTotal = $product->price * $qty;

                $orderItems[] = [
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'product_name' => $product->name,
                    'product_image' => $image->image_path ?? null,
                    'size' => $sizes[array_rand($sizes)],
                    'color' => $colors[array_rand($colors)][0],
                    'price' => $product->price,
                    'quantity' => $qty,
                    'subtotal' => $lineTotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $subtotal += $lineTotal;
            }

            DB::table('orders')->where('id', $order->id)->update([
                'subtotal' => $subtotal,
                'total_amount' => $subtotal + 30000,
            ]);
        }

        DB::table('order_items')->insert($orderItems);

        echo "✔ Product + Order data inserted successfully!\n";
    }
}
