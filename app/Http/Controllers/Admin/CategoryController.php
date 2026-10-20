<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        // Phân trang theo danh mục cấp 1, mỗi cấp 1 kèm danh mục con
        $categories = Category::whereNull('parent_id')
            ->withCount('children')
            ->with(['children' => fn ($q) => $q->withCount('products')->orderBy('name')])
            ->orderBy('name')
            ->paginate(10);

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.create', [
            'category'    => new Category(),
            'parents'     => $this->parentOptions(),
            'hasChildren' => false,
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->safe()->only(['name', 'parent_id']);

        if ($request->hasFile('image')) {
            // store() tự đặt tên ngẫu nhiên, không dùng tên tệp người dùng gửi
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Đã thêm danh mục.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', [
            'category'    => $category,
            'parents'     => $this->parentOptions($category),
            'hasChildren' => $category->children()->exists(),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->safe()->only(['name', 'parent_id']);

        if ($request->hasFile('image')) {
            $this->deleteImage($category->image);
            $data['image'] = $request->file('image')->store('categories', 'public');
        } elseif ($request->boolean('remove_image')) {
            $this->deleteImage($category->image);
            $data['image'] = null;
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Đã cập nhật danh mục.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->children()->exists()) {
            return back()->with('error', 'Không thể xóa: danh mục này còn danh mục con.');
        }

        // withTrashed: khóa ngoại vẫn chặn khi sản phẩm đã xóa mềm
        if ($category->products()->withTrashed()->exists()) {
            return back()->with('error', 'Không thể xóa: danh mục này còn sản phẩm (kể cả trong Thùng rác).');
        }

        $this->deleteImage($category->image);
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Đã xóa danh mục.');
    }

    private function parentOptions(?Category $except = null)
    {
        return Category::whereNull('parent_id')
            ->when($except, fn ($q) => $q->where('id', '!=', $except->id))
            ->orderBy('name')
            ->get();
    }

    private function deleteImage(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}