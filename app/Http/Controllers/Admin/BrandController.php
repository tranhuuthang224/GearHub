<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBrandRequest;
use App\Http\Requests\Admin\UpdateBrandRequest;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(): View
    {
        $brands = Brand::withCount('products')
            ->orderBy('name')
            ->paginate(10);

        return view('admin.brands.index', compact('brands'));
    }

    public function create(): View
    {
        return view('admin.brands.create', [
            'brand' => new Brand(),
        ]);
    }

    public function store(StoreBrandRequest $request): RedirectResponse
    {
        $data = $request->safe()->only(['name']);

        if ($request->hasFile('logo')) {
            // store() tự đặt tên ngẫu nhiên, không dùng tên tệp người dùng gửi
            $data['logo'] = $request->file('logo')->store('brands', 'public');
        }

        Brand::create($data);

        return redirect()->route('admin.brands.index')
            ->with('success', 'Đã thêm thương hiệu.');
    }

    public function edit(Brand $brand): View
    {
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(UpdateBrandRequest $request, Brand $brand): RedirectResponse
    {
        $data = $request->safe()->only(['name']);

        if ($request->hasFile('logo')) {
            $this->deleteImage($brand->logo);
            $data['logo'] = $request->file('logo')->store('brands', 'public');
        } elseif ($request->boolean('remove_logo')) {
            $this->deleteImage($brand->logo);
            $data['logo'] = null;
        }

        $brand->update($data);

        return redirect()->route('admin.brands.index')
            ->with('success', 'Đã cập nhật thương hiệu.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        // withTrashed: khóa ngoại vẫn chặn khi sản phẩm chỉ đang nằm trong Thùng rác
        if ($brand->products()->withTrashed()->exists()) {
            return back()->with('error', 'Không thể xóa: thương hiệu này còn sản phẩm (kể cả trong Thùng rác).');
        }

        $this->deleteImage($brand->logo);
        $brand->delete();

        return redirect()->route('admin.brands.index')
            ->with('success', 'Đã xóa thương hiệu.');
    }

    private function deleteImage(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}