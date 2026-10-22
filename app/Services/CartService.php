<?php

namespace App\Services;

use App\Models\Product;

class CartService
{
    /**
     * Lấy toàn bộ danh sách sản phẩm trong giỏ hàng từ Session.
     */
    public function getCart(): array
    {
        return session()->get('cart', []);
    }

    /**
     * Thêm sản phẩm vào giỏ hàng.
     */
    public function add(int|string $productId, int $quantity = 1): array
    {
        $quantity = max(1, (int) $quantity);
        $cart = $this->getCart();

        // 1. Tìm sản phẩm từ Database hoặc Mock Fallback
        $product = $this->findProduct($productId);

        if (!$product) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy thông tin sản phẩm.',
            ];
        }

        $stock = (int) ($product['stock_quantity'] ?? 10);
        if ($stock <= 0) {
            return [
                'success' => false,
                'message' => 'Sản phẩm này hiện đang tạm hết hàng.',
            ];
        }

        // 2. Tính toán số lượng mới
        $currentQty = isset($cart[$productId]) ? (int) $cart[$productId]['quantity'] : 0;
        $newQty = $currentQty + $quantity;

        if ($newQty > $stock) {
            $newQty = $stock;
            $message = "Số lượng trong giỏ đã đạt tối đa tồn kho ({$stock} sản phẩm).";
        } else {
            $message = "Đã thêm {$quantity} sản phẩm '{$product['name']}' vào giỏ hàng thành công!";
        }

        // 3. Đưa vào mảng session cart
        $cart[$productId] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'slug' => $product['slug'] ?? '#',
            'sku' => $product['sku'] ?? 'GEAR-ITEM',
            'category' => $product['category'] ?? 'Phụ kiện',
            'price' => (int) ($product['sale_price'] ?? $product['price']),
            'original_price' => (int) $product['price'],
            'thumbnail' => $product['thumbnail'] ?? asset('images/hero-headphone.png'),
            'quantity' => $newQty,
            'max_stock' => $stock,
        ];

        session()->put('cart', $cart);

        return [
            'success' => true,
            'message' => $message,
            'cartCount' => $this->getCount(),
            'subtotal' => $this->getSubtotal(),
            'total' => $this->getTotal(),
            'cart' => $cart,
        ];
    }

    /**
     * Cập nhật số lượng sản phẩm trong giỏ hàng.
     */
    public function update(int|string $productId, int $quantity): array
    {
        $cart = $this->getCart();

        if (!isset($cart[$productId])) {
            return [
                'success' => false,
                'message' => 'Sản phẩm không có trong giỏ hàng.',
            ];
        }

        $quantity = (int) $quantity;

        // Nếu số lượng <= 0 thì tự động xóa khỏi giỏ
        if ($quantity <= 0) {
            return $this->remove($productId);
        }

        $maxStock = (int) ($cart[$productId]['max_stock'] ?? 99);
        if ($quantity > $maxStock) {
            $quantity = $maxStock;
            $message = "Đã điều chỉnh về số lượng tối đa sẵn có ({$maxStock} sản phẩm).";
        } else {
            $message = 'Cập nhật số lượng thành công!';
        }

        $cart[$productId]['quantity'] = $quantity;
        session()->put('cart', $cart);

        $lineTotal = $cart[$productId]['price'] * $quantity;

        return [
            'success' => true,
            'message' => $message,
            'quantity' => $quantity,
            'lineTotal' => $lineTotal,
            'cartCount' => $this->getCount(),
            'subtotal' => $this->getSubtotal(),
            'shipping' => $this->getShippingFee(),
            'total' => $this->getTotal(),
        ];
    }

    /**
     * Xóa một sản phẩm khỏi giỏ hàng.
     */
    public function remove(int|string $productId): array
    {
        $cart = $this->getCart();

        if (isset($cart[$productId])) {
            $name = $cart[$productId]['name'];
            unset($cart[$productId]);
            session()->put('cart', $cart);

            return [
                'success' => true,
                'message' => "Đã xóa '{$name}' khỏi giỏ hàng.",
                'cartCount' => $this->getCount(),
                'subtotal' => $this->getSubtotal(),
                'shipping' => $this->getShippingFee(),
                'total' => $this->getTotal(),
            ];
        }

        return [
            'success' => false,
            'message' => 'Không tìm thấy sản phẩm trong giỏ hàng.',
        ];
    }

    /**
     * Xóa sạch toàn bộ giỏ hàng.
     */
    public function clear(): void
    {
        session()->forget('cart');
    }

    /**
     * Đếm tổng số lượng sản phẩm (Total quantity).
     */
    public function getCount(): int
    {
        $cart = $this->getCart();
        return array_sum(array_column($cart, 'quantity'));
    }

    /**
     * Tính tổng tiền hàng (Subtotal).
     */
    public function getSubtotal(): int
    {
        $cart = $this->getCart();
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += ((int) $item['price']) * ((int) $item['quantity']);
        }
        return $subtotal;
    }

    /**
     * Tính phí vận chuyển (Miễn phí từ 500.000đ, dưới 500.000đ là 30.000đ).
     */
    public function getShippingFee(): int
    {
        $subtotal = $this->getSubtotal();
        if ($subtotal === 0) {
            return 0;
        }
        return $subtotal >= 500000 ? 0 : 30000;
    }

    /**
     * Tổng thanh toán cuối cùng (Subtotal + Shipping).
     */
    public function getTotal(): int
    {
        return $this->getSubtotal() + $this->getShippingFee();
    }

    /**
     * Tìm sản phẩm theo ID từ DB hoặc Mock Catalog.
     */
    private function findProduct(int|string $productId): ?array
    {
        try {
            $dbProduct = Product::with('category')->find($productId);
            if ($dbProduct) {
                $thumb = $dbProduct->thumbnail 
                    ? (str_starts_with($dbProduct->thumbnail, 'http') ? $dbProduct->thumbnail : asset('storage/' . $dbProduct->thumbnail))
                    : asset('images/png-transparent-playstation-4-headphones-hyperx-cloud-audio-video-game-headphones-electronics-video-game-playstation-4.png');

                return [
                    'id' => $dbProduct->id,
                    'name' => $dbProduct->name,
                    'slug' => $dbProduct->slug,
                    'sku' => $dbProduct->sku,
                    'category' => $dbProduct->category?->name ?? 'Phụ kiện',
                    'price' => (int) $dbProduct->price,
                    'sale_price' => $dbProduct->sale_price ? (int) $dbProduct->sale_price : null,
                    'stock_quantity' => (int) $dbProduct->stock_quantity,
                    'thumbnail' => $thumb,
                ];
            }
        } catch (\Throwable $e) {
            // DB not connected, proceed to mock catalog fallback
        }

        // Mock Fallback Catalog for local testing
        $mockCatalog = [
            101 => [
                'id' => 101,
                'name' => 'Tai nghe Gaming HyperX Cloud III Wireless Red',
                'slug' => 'tai-nghe-gaming-hyperx-cloud-iii-wireless-red',
                'sku' => 'HX-CL3W-RD',
                'category' => 'Tai nghe',
                'price' => 2990000,
                'sale_price' => 2490000,
                'stock_quantity' => 25,
                'thumbnail' => asset('images/png-transparent-playstation-4-headphones-hyperx-cloud-audio-video-game-headphones-electronics-video-game-playstation-4.png'),
            ],
            102 => [
                'id' => 102,
                'name' => 'Chuột Razer Basilisk V3 Pro Wireless',
                'slug' => 'razer-basilisk-v3-pro',
                'sku' => 'RZ-BV3P-WL',
                'category' => 'Chuột',
                'price' => 3890000,
                'sale_price' => 3290000,
                'stock_quantity' => 15,
                'thumbnail' => asset('images/mouse-razer.png'),
            ],
            103 => [
                'id' => 103,
                'name' => 'Bàn phím cơ không dây Keychron K2 Pro',
                'slug' => 'keychron-k2-pro',
                'sku' => 'KC-K2P-RGB',
                'category' => 'Bàn phím cơ',
                'price' => 2390000,
                'sale_price' => null,
                'stock_quantity' => 18,
                'thumbnail' => asset('images/keyboard-katana.png'),
            ],
            104 => [
                'id' => 104,
                'name' => 'Micro HyperX QuadCast S RGB USB',
                'slug' => 'hyperx-quadcast-s',
                'sku' => 'HX-MIC-QC',
                'category' => 'Microphone',
                'price' => 3690000,
                'sale_price' => 3290000,
                'stock_quantity' => 10,
                'thumbnail' => asset('images/images (2).png'),
            ],
            105 => [
                'id' => 105,
                'name' => 'Chuột Gaming ATK F1 Ultimate Carbon',
                'slug' => 'atk-f1-ultimate',
                'sku' => 'ATK-F1-ULT',
                'category' => 'Chuột',
                'price' => 1990000,
                'sale_price' => 1690000,
                'stock_quantity' => 20,
                'thumbnail' => asset('images/mouse-atk.png'),
            ],
        ];

        return $mockCatalog[$productId] ?? null;
    }
}
