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
     * Tìm sản phẩm theo ID từ DB hoặc Catalog.
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
            // DB not connected, proceed to catalog fallback
        }

        // Tra cứu trong ProductCatalog
        $item = ProductCatalog::find($productId);

        if (!$item && is_numeric($productId)) {
            $fallbackMap = [
                1 => 401,
                2 => 201,
                3 => 501,
                4 => 408,
                5 => 403,
                6 => 209,
                7 => 504,
                8 => 301,
            ];
            $mappedId = $fallbackMap[(int) $productId] ?? null;
            if ($mappedId) {
                $item = ProductCatalog::find($mappedId);
            }
        }

        if ($item) {
            return [
                'id' => $item['id'],
                'name' => $item['name'],
                'slug' => $item['slug'],
                'sku' => $item['sku'] ?? 'GEAR-' . $item['id'],
                'category' => $item['category'] ?? 'Phụ kiện',
                'price' => (int) $item['price'],
                'sale_price' => !empty($item['sale_price']) ? (int) $item['sale_price'] : null,
                'stock_quantity' => (int) ($item['stock_quantity'] ?? 20),
                'thumbnail' => $item['thumbnail'],
            ];
        }

        return null;
    }
}
