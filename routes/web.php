<?php

use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\ShopController;
use App\Http\Controllers\Client\CartController;
use App\Http\Controllers\Client\CompareController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductTrashController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Client\CheckoutCouponController;
use App\Http\Controllers\Client\CheckoutController;
use App\Http\Controllers\Client\OrderController as ClientOrderController;
use App\Http\Controllers\Admin\OrderController;

// Storefront Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{slug}', [ShopController::class, 'show'])->name('shop.show');

// Cart Routes (Session-based)
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::patch('/update', [CartController::class, 'update'])->name('update');
    Route::delete('/remove/{id}', [CartController::class, 'remove'])->name('remove');
    Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
});

// Comparison Routes (Session-based)
Route::prefix('compare')->name('compare.')->group(function () {
    Route::get('/', [CompareController::class, 'index'])->name('index');
    Route::get('/items', [CompareController::class, 'items'])->name('items');
    Route::post('/add/{id}', [CompareController::class, 'add'])->name('add');
    Route::post('/toggle/{id}', [CompareController::class, 'toggle'])->name('toggle');
    Route::delete('/remove/{id}', [CompareController::class, 'remove'])->name('remove');
    Route::delete('/clear', [CompareController::class, 'clear'])->name('clear');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::redirect('/', '/admin/dashboard');

        // Tạm thời, Dashboard thật làm ở tuần 7 (khi đó đổi sang controller)
        Route::view('/dashboard', 'admin.dashboard')->name('dashboard');

        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('brands', BrandController::class)->except(['show']);
        Route::resource('products', ProductController::class)->except(['show']);

        // Thùng rác sản phẩm: URL /admin/trash khớp mục Trash của sidebar
        Route::get('trash', [ProductTrashController::class, 'index'])->name('trash.index');
        Route::post('trash/{product}/restore', [ProductTrashController::class, 'restore'])
            ->withTrashed()->name('trash.restore');
        Route::delete('trash/{product}', [ProductTrashController::class, 'destroy'])
            ->withTrashed()->name('trash.destroy');
        
        Route::resource('coupons', CouponController::class)->except(['show']);
        Route::resource('orders', OrderController::class)->only(['index', 'show']);
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
    });

// Checkout & Order Routes (Requires Auth)
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/success/{code}', [CheckoutController::class, 'success'])->name('checkout.success');

    // Đơn hàng của tôi (Client Orders)
    Route::get('/orders', [ClientOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{code}', [ClientOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{code}/cancel', [ClientOrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/orders/{code}/reorder', [ClientOrderController::class, 'reorder'])->name('orders.reorder');
});

// Xem trước mã giảm giá ở Checkout (B). Giới hạn 20 lượt/phút để chống dò mã.
Route::middleware(['auth', 'throttle:20,1'])
    ->post('/checkout/coupon', CheckoutCouponController::class)
    ->name('checkout.coupon');

require __DIR__.'/auth.php';
