<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCouponRequest;
use App\Http\Requests\Admin\UpdateCouponRequest;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        // Bộ lọc kích hoạt: on (đang bật), off (đang tắt); giá trị lạ coi như không lọc
        $status = $request->query('status');
        $status = in_array($status, ['on', 'off'], true) ? $status : null;

        $coupons = Coupon::query()
            ->when($status === 'on', fn ($q) => $q->where('is_active', true))
            ->when($status === 'off', fn ($q) => $q->where('is_active', false))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        // Vừa xóa mục cuối của trang cuối: chuyển về trang cuối còn dữ liệu
        if ($coupons->isEmpty() && $coupons->currentPage() > 1) {
            return redirect()->route(
                'admin.coupons.index',
                array_merge($request->query(), ['page' => $coupons->lastPage()])
            );
        }

        return view('admin.coupons.index', [
            'coupons' => $coupons,
            'status'  => $status,
        ]);
    }

    public function create(): View
    {
        return view('admin.coupons.create', [
            'coupon' => new Coupon([
                'type'      => Coupon::TYPE_PERCENT,
                'min_order' => 0,
                'is_active' => true,
            ]),
        ]);
    }

    public function store(StoreCouponRequest $request): RedirectResponse
    {
        $data = $request->safe()->only([
            'code', 'type', 'value', 'min_order', 'usage_limit', 'starts_at', 'expires_at',
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $coupon = Coupon::create($data);

        return redirect()->route('admin.coupons.index')
            ->with('success', "Đã thêm mã giảm giá «{$coupon->code}».");
    }

    public function edit(Coupon $coupon): View
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $data = $request->safe()->only([
            'code', 'type', 'value', 'min_order', 'usage_limit', 'starts_at', 'expires_at',
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $coupon->update($data);

        return redirect()->route('admin.coupons.index')
            ->with('success', "Đã cập nhật mã giảm giá «{$coupon->code}».");
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        // orders.coupon_id là nullOnDelete: xóa mã đã dùng sẽ làm đơn cũ mất mã, nên chặn
        if ($coupon->orders()->exists()) {
            return back()->with(
                'error',
                "Không thể xóa «{$coupon->code}»: mã đã được dùng trong đơn hàng. Hãy tắt kích hoạt thay vì xóa."
            );
        }

        $code = $coupon->code;
        $coupon->delete();

        return back()->with('success', "Đã xóa mã giảm giá «{$code}».");
    }
}