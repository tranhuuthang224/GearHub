<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function create(): View
    {
        return view('admin.products.create', $this->formData(new Product()));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        // safe()->only(): chỉ lấy các cột của bảng products, bỏ qua tệp ảnh và specs thô
        $data = $request->safe()->only([
            'category_id', 'brand_id', 'name', 'sku', 'description',
            'price', 'sale_price', 'stock_quantity',
        ]);

        $data['specs']     = $request->specsPayload();
        $data['is_active'] = $request->boolean('is_active');

        // TODO Thứ 6: lưu thumbnail và images[] (tệp đã được validate ở Form Request)
        $product = Product::create($data);

        // TODO Thứ 6: đổi sang route('admin.products.index')
        return redirect()->route('admin.products.create')
            ->with('success', 'Đã thêm sản phẩm «' . $product->name . '».');
    }

    /** Dữ liệu dùng chung cho form tạo và form sửa (Thứ 7). */
    private function formData(Product $product): array
    {
        return [
            'product'        => $product,
            // Chỉ danh mục cấp 1 có con, kèm các con để hiện dạng nhóm <optgroup>
            'categoryGroups' => Category::whereNull('parent_id')
                ->whereHas('children')
                ->with(['children' => fn ($q) => $q->orderBy('name')])
                ->orderBy('name')
                ->get(),
            'brands'         => Brand::orderBy('name')->get(['id', 'name']),
        ];
    }
}