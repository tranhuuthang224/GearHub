<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductTrashController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $products = Product::onlyTrashed()
            ->with(['category.parent', 'brand'])
            ->withCount('orderItems')                 // sản phẩm đã có trong đơn thì không xóa vĩnh viễn được
            ->latest('deleted_at')
            ->latest('id')                            // thứ tự ổn định khi nhiều sản phẩm cùng giờ xóa
            ->paginate(10)
            ->withQueryString();

        // Vừa xóa mục cuối của trang cuối: chuyển về trang cuối còn dữ liệu
        if ($products->isEmpty() && $products->currentPage() > 1) {
            return redirect()->route(
                'admin.trash.index',
                array_merge($request->query(), ['page' => $products->lastPage()])
            );
        }

        return view('admin.trash.index', compact('products'));
    }

    public function restore(Product $product): RedirectResponse
    {
        abort_unless($product->trashed(), 404);

        $product->restore();

        return back()->with('success', "Đã khôi phục sản phẩm «{$product->name}».");
    }

    public function destroy(Product $product): RedirectResponse
    {
        // Không cho "xóa vĩnh viễn" một sản phẩm còn đang bán: phải qua Thùng rác trước
        abort_unless($product->trashed(), 404);

        if ($product->orderItems()->exists()) {
            return back()->with(
                'error',
                "Không thể xóa vĩnh viễn «{$product->name}»: sản phẩm đã có trong đơn hàng. Hãy giữ nó trong Thùng rác để bảo toàn lịch sử đơn."
            );
        }

        // Đọc đường dẫn tệp TRƯỚC khi xóa: các dòng product_images sẽ bị khóa ngoại xóa theo
        $paths = $product->images()->pluck('path')->all();

        if ($product->thumbnail) {
            $paths[] = $product->thumbnail;
        }

        try {
            DB::transaction(fn () => $product->forceDelete());
        } catch (QueryException $e) {
            // Khóa ngoại chặn lại (vd. một đơn vừa chứa sản phẩm này giữa lúc kiểm tra và xóa)
            report($e);

            return back()->with(
                'error',
                "Không thể xóa vĩnh viễn «{$product->name}»: dữ liệu đang được sử dụng ở nơi khác."
            );
        }

        $this->deleteFiles($paths);

        return back()->with('success', "Đã xóa vĩnh viễn sản phẩm «{$product->name}» cùng ảnh.");
    }

    /** Xóa tệp ảnh, rồi dọn thư mục nếu đã trống (không bao giờ đụng thư mục gốc "products"). */
    private function deleteFiles(array $paths): void
    {
        $disk = Storage::disk('public');

        $disk->delete($paths);

        foreach (array_unique(array_map('dirname', $paths)) as $directory) {
            if (! in_array($directory, ['', '.', 'products'], true) && $disk->allFiles($directory) === []) {
                $disk->deleteDirectory($directory);
            }
        }
    }
}