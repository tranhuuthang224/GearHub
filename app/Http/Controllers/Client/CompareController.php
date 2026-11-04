<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\CompareService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    public function __construct(
        protected CompareService $compareService
    ) {}

    /**
     * Xem trang so sánh chi tiết hoặc lấy danh sách so sánh hiện tại.
     */
    public function index(Request $request)
    {
        $products = $this->compareService->getItems();

        // Nếu request AJAX/JSON từ Alpine.js của Member A (ví dụ thanh Compare Bar)
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'count'    => $products->count(),
                'products' => $products,
            ]);
        }

        // Trả về view Blade trang so sánh chi tiết
        return view('client.compare.index', compact('products'));
    }

    /**
     * Thêm sản phẩm vào so sánh: POST /compare/add/{id}
     */
    public function add(Request $request, int|string $id)
    {
        $result = $this->compareService->add($id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Xóa sản phẩm khỏi so sánh: DELETE /compare/remove/{id}
     */
    public function remove(Request $request, int|string $id)
    {
        $result = $this->compareService->remove($id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Xóa tất cả: DELETE /compare/clear
     */
    public function clear(Request $request)
    {
        $this->compareService->clear();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa tất cả sản phẩm so sánh.',
                'count'   => 0,
            ]);
        }

        return redirect()->route('shop.index')->with('success', 'Đã xóa danh sách so sánh.');
    }
}