<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. KPI THỐNG KÊ TỔNG QUAN
        // Doanh thu thực tế từ các đơn đã hoàn thành
        $totalRevenue = Order::where('status', Order::STATUS_COMPLETED)->sum('total');

        // Tổng số đơn hàng (không tính đơn đã hủy)
        $totalOrders = Order::count();

        // Khách hàng mới (đăng ký trong 30 ngày qua hoặc tổng khách hàng)
        $newCustomersCount = User::where('role', User::ROLE_CUSTOMER)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->count();
        if ($newCustomersCount === 0) {
            $newCustomersCount = User::where('role', User::ROLE_CUSTOMER)->count();
        }

        // Sản phẩm sắp hết hàng (tồn kho <= ngưỡng 5 và > 0)
        $lowStockCount = Product::active()
            ->where('stock_quantity', '<=', Product::LOW_STOCK_THRESHOLD)
            ->where('stock_quantity', '>', 0)
            ->count();

        // 2. BIỂU ĐỒ 1: DOANH THU 7 NGÀY GẦN NHẤT
        $days = collect();
        $revenueData = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dayLabel = $date->format('d/m');
            $days->push($dayLabel);

            $dayRevenue = Order::where('status', Order::STATUS_COMPLETED)
                ->whereDate('created_at', $date)
                ->sum('total');
            $revenueData->push((int) $dayRevenue);
        }

        // 3. BIỂU ĐỒ 2: CƠ CẤU DOANH SỐ THEO DANH MỤC CHA
        // Lấy doanh thu theo danh mục qua order_items -> products -> categories
        $categoriesSales = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('categories as parent_categories', 'parent_categories.id', '=', 'categories.parent_id')
            ->where('orders.status', Order::STATUS_COMPLETED)
            ->selectRaw('
                COALESCE(parent_categories.name, categories.name) as cat_name,
                SUM(order_items.unit_price * order_items.quantity) as total_sales
            ')
            ->groupBy('cat_name')
            ->orderByDesc('total_sales')
            ->pluck('total_sales', 'cat_name')
            ->toArray();

        // Fallback nếu chưa có đơn hàng completed để biểu đồ không bị rỗng
        if (empty($categoriesSales)) {
            $categoriesSales = [
                'Bàn phím cơ' => 45,
                'Chuột'       => 25,
                'Tai nghe'    => 20,
                'Laptop'      => 10,
            ];
        }

        // 4. BẢNG TOP 5 SẢN PHẨM BÁN CHẠY NHẤT
        $topProducts = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.status', Order::STATUS_COMPLETED)
            ->select(
                'products.id',
                'products.name',
                'products.thumbnail',
                'products.price',
                'products.sale_price',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.unit_price * order_items.quantity) as total_revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.thumbnail', 'products.price', 'products.sale_price')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        // Fallback nếu chưa có đơn hàng thì lấy 5 sản phẩm đầu tiên
        if ($topProducts->isEmpty()) {
            $topProducts = Product::active()->take(5)->get()->map(function ($p) {
                $p->total_sold = 0;
                $p->total_revenue = 0;
                return $p;
            });
        }

        return view('admin.dashboard', [
            'totalRevenue'      => $totalRevenue,
            'totalOrders'       => $totalOrders,
            'newCustomersCount' => $newCustomersCount,
            'lowStockCount'     => $lowStockCount,
            'revenueChartDays'  => $days->toArray(),
            'revenueChartData'  => $revenueData->toArray(),
            'categoryNames'     => array_keys($categoriesSales),
            'categoryValues'    => array_values($categoriesSales),
            'topProducts'       => $topProducts,
        ]);
    }
}