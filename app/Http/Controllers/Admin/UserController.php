<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Danh sách tài khoản khách hàng
     * GET /admin/users
     */
    public function index(Request $request)
    {
        $query = User::where('role', User::ROLE_CUSTOMER)
            ->withCount('orders')
            ->latest();

        // Tìm kiếm theo tên, email hoặc SĐT
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Lọc theo trạng thái Hoạt động / Đang khóa
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $users = $query->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Xem chi tiết thông tin và lịch sử mua hàng của khách
     * GET /admin/users/{user}
     */
    public function show(User $user)
    {
        // Lấy lịch sử các đơn hàng kèm tổng tiền
        $orders = $user->orders()
            ->with('items')
            ->latest()
            ->paginate(10);

        $totalSpent = $user->orders()
            ->where('status', Order::STATUS_COMPLETED)
            ->sum('total');

        $completedOrdersCount = $user->orders()
            ->where('status', Order::STATUS_COMPLETED)
            ->count();

        return view('admin.users.show', compact('user', 'orders', 'totalSpent', 'completedOrdersCount'));
    }

    /**
     * Khóa hoặc Mở khóa tài khoản khách hàng
     * PATCH /admin/users/{user}/toggle-status
     */
    public function toggleStatus(User $user)
    {
        // Chặn tự khóa chính tài khoản admin đang đăng nhập
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Bạn không thể tự khóa tài khoản của chính mình.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $action = $user->is_active ? 'Mở khóa' : 'Khóa';
        return back()->with('success', "Đã {$action} tài khoản «{$user->name}» thành công.");
    }
}