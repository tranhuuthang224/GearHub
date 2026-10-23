<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): View
{
    $search     = trim((string) $request->query('q', ''));
    $categoryId = $request->integer('category');
    $brandId    = $request->integer('brand');

    $products = Product::query()
        ->with(['category.parent', 'brand'])          // tránh N+1
        ->when($search !== '', function ($query) use ($search) {
            $like = '%' . $search . '%';
            $query->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('sku', 'like', $like));
        })
        ->when($categoryId > 0, fn ($query) => $query->inCategory($categoryId))
        ->when($brandId > 0, fn ($query) => $query->where('brand_id', $brandId))
        ->latest('id')
        ->paginate(10)
        ->withQueryString();                           // giữ bộ lọc khi chuyển trang

    return view('admin.products.index', [
        'products'       => $products,
        'categoryGroups' => $this->categoryGroups(),
        'brands'         => $this->brandOptions(),
        'filters'        => ['q' => $search, 'category' => $categoryId, 'brand' => $brandId],
        'hasFilters'     => $search !== '' || $categoryId > 0 || $brandId > 0,
    ]);
}
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

        $storedPaths = [];

        try {
            $product = DB::transaction(function () use ($request, $data, &$storedPaths) {
                $product   = Product::create($data);
                $directory = "products/{$product->id}";

                if ($request->hasFile('thumbnail')) {
                    // store() tự đặt tên ngẫu nhiên, không dùng tên tệp người dùng gửi
                    $path = $request->file('thumbnail')->store($directory, 'public');
                    $storedPaths[] = $path;
                    $product->update(['thumbnail' => $path]);
                }

                foreach ($request->file('images', []) as $index => $file) {
                    $path = $file->store($directory, 'public');
                    $storedPaths[] = $path;

                    $product->images()->create([
                        'path'       => $path,
                        'sort_order' => $index + 1,
                    ]);
                }

                return $product;
            });
        } catch (\Throwable $e) {
            // Ghi CSDL lỗi thì transaction tự rollback; xóa luôn các tệp đã lỡ lưu để không để ảnh mồ côi
            Storage::disk('public')->delete($storedPaths);

            throw $e;
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Đã thêm sản phẩm «' . $product->name . '».');
    }

    /** Dữ liệu dùng chung cho form tạo và form sửa (Thứ 7). */
    private function formData(Product $product): array
    {
        return [
            'product'        => $product,
            'categoryGroups' => $this->categoryGroups(),
            'brands'         => $this->brandOptions(),
        ];
    }

    /** Danh mục cấp 1 có con, kèm các con, dùng cho ô chọn dạng nhóm (<optgroup>). */
    private function categoryGroups()
    {
        return Category::whereNull('parent_id')
            ->whereHas('children')
            ->with(['children' => fn ($q) => $q->orderBy('name')])
            ->orderBy('name')
            ->get();
    }

    private function brandOptions()
    {
        return Brand::orderBy('name')->get(['id', 'name']);
    }
}