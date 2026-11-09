@extends('layouts.admin', ['title' => 'Báo cáo & Thống kê Kinh doanh'])

@section('content')
<div class="space-y-6">

    <!-- Header Tiêu đề -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Dashboard Quản trị</h1>
            <p class="text-sm text-gray-500 mt-1">Tổng quan hoạt động kinh doanh, doanh thu và dữ liệu khách hàng theo thời gian thực.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Hệ thống hoạt động ổn định
            </span>
        </div>
    </div>

    <!-- 1. 4 THẺ KPI TỔNG QUAN -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- KPI 1: Doanh thu thực tế -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Doanh thu thực tế</p>
                <h3 class="text-2xl font-extrabold text-gray-900 mt-1">{{ number_format($totalRevenue, 0, ',', '.') }} ₫</h3>
                <p class="text-xs text-emerald-600 font-medium mt-1">✓ Từ các đơn đã hoàn thành</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- KPI 2: Tổng đơn hàng -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tổng đơn hàng</p>
                <h3 class="text-2xl font-extrabold text-gray-900 mt-1">{{ number_format($totalOrders) }}</h3>
                <p class="text-xs text-gray-500 mt-1">Đơn đặt qua hệ thống</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            </div>
        </div>

        <!-- KPI 3: Khách hàng mới -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Khách hàng mới</p>
                <h3 class="text-2xl font-extrabold text-gray-900 mt-1">{{ number_format($newCustomersCount) }}</h3>
                <p class="text-xs text-indigo-600 font-medium mt-1">Tài khoản thành viên</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
        </div>

        <!-- KPI 4: Sắp hết hàng -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Sắp hết hàng</p>
                <h3 class="text-2xl font-extrabold {{ $lowStockCount > 0 ? 'text-amber-600' : 'text-gray-900' }} mt-1">{{ $lowStockCount }}</h3>
                <p class="text-xs text-amber-600 font-medium mt-1">Tồn kho ≤ 5 sản phẩm</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- 2. KHU VỰC BIỂU ĐỒ CHART.JS -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Biểu đồ đường: Doanh thu 7 ngày gần nhất -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-gray-900">Xu hướng Doanh thu (7 ngày gần nhất)</h3>
                    <p class="text-xs text-gray-500">Biểu đồ thể hiện biến động dòng tiền thực tế theo ngày</p>
                </div>
            </div>
            <div class="relative h-72">
                <canvas id="revenueTrendChart"></canvas>
            </div>
        </div>

        <!-- Biểu đồ tròn: Cơ cấu doanh số theo danh mục -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <div class="mb-4">
                <h3 class="font-bold text-gray-900">Cơ cấu Doanh số theo Ngành</h3>
                <p class="text-xs text-gray-500">Tỷ lệ đóng góp theo danh mục sản phẩm</p>
            </div>
            <div class="relative h-72 flex items-center justify-center">
                <canvas id="categorySalesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- 3. BẢNG TOP 5 SẢN PHẨM BÁN CHẠY NHẤT -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-gray-900">Top 5 Sản phẩm Bán chạy nhất</h3>
                <p class="text-xs text-gray-500 mt-0.5">Xếp hạng theo số lượng đã bán qua các đơn hoàn thành</p>
            </div>
            <a href="{{ route('admin.products.index') }}" class="text-xs font-semibold text-primary hover:underline">Xem tất cả sản phẩm &rarr;</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50/75 text-xs uppercase font-semibold text-gray-500">
                    <tr>
                        <th class="px-6 py-3.5">Sản phẩm</th>
                        <th class="px-6 py-3.5">Giá bán</th>
                        <th class="px-6 py-3.5 text-center">Đã bán</th>
                        <th class="px-6 py-3.5 text-right">Tổng doanh thu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($topProducts as $item)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 flex items-center gap-3">
                                <img src="{{ $item->thumbnail_url ?? asset($item->thumbnail) }}" alt="{{ $item->name }}" class="w-10 h-10 object-contain rounded-lg border border-gray-200 bg-white p-1">
                                <div>
                                    <p class="font-semibold text-gray-900 line-clamp-1">{{ $item->name }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ number_format($item->effective_price ?? $item->price, 0, ',', '.') }} ₫
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-primary/10 text-primary">
                                    {{ $item->total_sold }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-gray-900">
                                {{ number_format($item->total_revenue ?? 0, 0, ',', '.') }} ₫
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400">Chưa có dữ liệu bán hàng.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- NHÚNG CHART.JS TỪ CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Biểu đồ Đường: Doanh thu 7 ngày
        const ctxRevenue = document.getElementById('revenueTrendChart').getContext('2d');
        new Chart(ctxRevenue, {
            type: 'line',
            data: {
                labels: @json($revenueChartDays),
                datasets: [{
                    label: 'Doanh thu (₫)',
                    data: @json($revenueChartData),
                    borderColor: '#2563EB',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#2563EB',
                    pointRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return (value / 1000000).toFixed(1) + ' tr';
                            }
                        }
                    }
                }
            }
        });

        // 2. Biểu đồ Tròn (Doughnut): Cơ cấu theo danh mục
        const ctxCategory = document.getElementById('categorySalesChart').getContext('2d');
        new Chart(ctxCategory, {
            type: 'doughnut',
            data: {
                labels: @json($categoryNames),
                datasets: [{
                    data: @json($categoryValues),
                    backgroundColor: [
                        '#2563EB', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899', '#6B7280'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { size: 11 } }
                    }
                },
                cutout: '65%'
            }
        });
    });
</script>
@endsection