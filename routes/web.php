<?php

use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CommunityModerationController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductTrashController;
use App\Http\Controllers\Client\CartController;
use App\Http\Controllers\Client\CheckoutController;
use App\Http\Controllers\Client\CheckoutCouponController;
use App\Http\Controllers\Client\CommunityController;
use App\Http\Controllers\Client\CompareController;
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\OrderController as ClientOrderController;
use App\Http\Controllers\Client\QuizController;
use App\Http\Controllers\Client\ReviewController;
use App\Http\Controllers\Client\ShopController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;

// Storefront Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
// Public API Documentation Route
Route::view('/docs/api', 'docs.api')->name('docs.api');
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{slug}', [ShopController::class, 'show'])->name('shop.show');
Route::post('/shop/{slug}/reviews', [ReviewController::class, 'store'])->middleware('auth')->name('shop.reviews.store');
Route::delete('/shop/{slug}/reviews', [ReviewController::class, 'destroy'])->middleware('auth')->name('shop.reviews.destroy');

// Product Reviews API Routes (Hỗ trợ cả route /products/{product}/reviews)
Route::get('/products/{product}/reviews', [ReviewController::class, 'index'])->name('products.reviews.index');
Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])
    ->middleware(['auth', 'throttle:reviews-submit'])
    ->name('products.reviews.store');

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

// Smart Gear Finder Quiz Routes
Route::prefix('quiz')->name('quiz.')->group(function () {
    Route::get('/', [QuizController::class, 'index'])->name('index');
    Route::post('/submit', [QuizController::class, 'submit'])
        ->middleware('throttle:quiz-submit')
        ->name('submit');
});

// Community Showcase Routes (Diễn đàn khoe góc máy)
Route::prefix('community')->name('community.')->group(function () {
    Route::get('/', [CommunityController::class, 'index'])->name('index');
    Route::get('/create', [CommunityController::class, 'create'])->middleware('auth')->name('create');
    Route::post('/', [CommunityController::class, 'store'])->middleware('auth')->name('store');
    Route::get('/search-products', [CommunityController::class, 'searchProducts'])->name('search-products');
    Route::get('/{slug}', [CommunityController::class, 'show'])->name('show');

    // Route Like: Không dùng middleware auth để Controller tự trả về JSON 401
    Route::post('/{post}/like', [CommunityController::class, 'like'])
        ->middleware('throttle:community-interactions')
        ->name('like');

    // Route Comment: Yêu cầu đăng nhập + Rate Limit
    Route::post('/{post}/comments', [CommunityController::class, 'comment'])
        ->middleware(['auth', 'throttle:community-interactions'])
        ->name('comment');
        
    // Route Comment Alternative (nếu frontend cần dùng)
    Route::post('/{post}/comments-alt', [CommunityController::class, 'comment'])
        ->middleware(['auth', 'throttle:community-interactions'])
        ->name('comments.store');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Sổ địa chỉ nhận hàng
    Route::post('/profile/addresses', [ProfileController::class, 'storeAddress'])->name('profile.addresses.store');
    Route::put('/profile/addresses/{address}', [ProfileController::class, 'updateAddress'])->name('profile.addresses.update');
    Route::delete('/profile/addresses/{address}', [ProfileController::class, 'destroyAddress'])->name('profile.addresses.destroy');
    Route::post('/profile/addresses/{address}/default', [ProfileController::class, 'setDefaultAddress'])->name('profile.addresses.default');

    // Quản lý bài viết góc máy cá nhân
    Route::delete('/profile/community-posts/{post}', [ProfileController::class, 'destroyPost'])->name('profile.community-posts.destroy');
});

// Admin Control Panel Routes
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::redirect('/', '/admin/dashboard');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('brands', BrandController::class)->except(['show']);
        Route::resource('products', ProductController::class)->except(['show']);
        Route::redirect('inventory', '/admin/products');

        // Thùng rác sản phẩm: URL /admin/trash khớp mục Trash của sidebar
        Route::get('trash', [ProductTrashController::class, 'index'])->name('trash.index');
        Route::post('trash/{product}/restore', [ProductTrashController::class, 'restore'])
            ->withTrashed()->name('trash.restore');
        Route::delete('trash/{product}', [ProductTrashController::class, 'destroy'])
            ->withTrashed()->name('trash.destroy');
        
        Route::resource('coupons', CouponController::class)->except(['show']);
        Route::resource('orders', OrderController::class)->only(['index', 'show']);
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');

        // Community Moderation (Đặt chính xác trong group Admin bảo mật)
        Route::get('community', [CommunityModerationController::class, 'index'])->name('community.index');
        Route::patch('community/{post}/status', [CommunityModerationController::class, 'updateStatus'])->name('community.status');
        Route::delete('community/{post}', [CommunityModerationController::class, 'destroy'])->name('community.destroy');

        // Quản lý khách hàng
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    });

// Checkout & Order Routes (Requires Auth)
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])
        ->middleware('throttle:checkout-order')
        ->name('checkout.store')
        ->block($lockSeconds = 10, $waitSeconds = 10);
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
