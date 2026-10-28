<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View|RedirectResponse
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

        // Vừa xóa mục cuối của trang cuối thì trang hiện tại thành rỗng:
        // chuyển về trang cuối còn dữ liệu (lastPage() tối thiểu là 1 nên không lặp vô hạn)
        if ($products->isEmpty() && $products->currentPage() > 1) {
            return redirect()->route(
                'admin.products.index',
                array_merge($request->query(), ['page' => $products->lastPage()])
            );
        }

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

    public function edit(Product $product): View
    {
        $product->load('images');

        return view('admin.products.edit', $this->formData($product));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->safe()->only([
            'category_id', 'brand_id', 'name', 'sku', 'description',
            'price', 'sale_price', 'stock_quantity',
        ]);

        $data['specs']     = $request->specsPayload();
        $data['is_active'] = $request->boolean('is_active');

        $newPaths = []; // tệp vừa lưu: xóa đi nếu có lỗi
        $oldPaths = []; // tệp cũ bị thay hoặc bị xóa: chỉ xóa sau khi CSDL đã lưu thành công

        try {
            DB::transaction(function () use ($request, $product, $data, &$newPaths, &$oldPaths) {
                $directory = "products/{$product->id}";

                if ($request->hasFile('thumbnail')) {
                    if ($product->thumbnail) {
                        $oldPaths[] = $product->thumbnail;
                    }

                    $path = $request->file('thumbnail')->store($directory, 'public');
                    $newPaths[] = $path;
                    $data['thumbnail'] = $path;
                } elseif ($request->boolean('remove_thumbnail')) {
                    if ($product->thumbnail) {
                        $oldPaths[] = $product->thumbnail;
                    }

                    $data['thumbnail'] = null;
                }

                $product->update($data);

                // Xóa các ảnh chi tiết được tick (Form Request đã bảo đảm chúng thuộc sản phẩm này)
                $removeIds = array_map('intval', (array) $request->input('remove_images', []));

                if ($removeIds) {
                    foreach ($product->images()->whereIn('id', $removeIds)->get() as $image) {
                        $oldPaths[] = $image->path;
                        $image->delete();
                    }
                }

                // Ảnh mới nối tiếp thứ tự hiện có
                $nextOrder = (int) $product->images()->max('sort_order');

                foreach ($request->file('images', []) as $file) {
                    $path = $file->store($directory, 'public');
                    $newPaths[] = $path;

                    $product->images()->create([
                        'path'       => $path,
                        'sort_order' => ++$nextOrder,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($newPaths);

            throw $e;
        }

        Storage::disk('public')->delete($oldPaths);

        return redirect()->route('admin.products.index')
            ->with('success', 'Đã cập nhật sản phẩm «' . $product->name . '».');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $name = $product->name;

        // SoftDeletes: chỉ đặt deleted_at. Giữ nguyên bản ghi, ảnh và tệp để Khôi phục được ở Tuần 4,
        // và để đơn hàng cũ vẫn đọc được sản phẩm (OrderItem dùng withTrashed).
        $product->delete();

        return back()->with('success', "Đã chuyển sản phẩm «{$name}» vào Thùng rác.");
    }

    /** Dữ liệu dùng chung cho form tạo và form sửa. */
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