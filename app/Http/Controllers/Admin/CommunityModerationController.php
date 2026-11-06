<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use Illuminate\Http\Request;

class CommunityModerationController extends Controller
{
    /**
     * Danh sách bài đăng trong Admin kèm bộ lọc trạng thái
     * GET /admin/community
     */
    public function index(Request $request)
    {
        $query = CommunityPost::with(['user', 'products'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $posts = $query->paginate(15)->withQueryString();

        return view('admin.community.index', compact('posts'));
    }

    /**
     * Cập nhật trạng thái bài đăng (Duyệt, Ẩn vi phạm)
     * PATCH /admin/community/{post}/status
     */
    public function updateStatus(Request $request, CommunityPost $post)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', [
                CommunityPost::STATUS_PENDING,
                CommunityPost::STATUS_PUBLISHED,
                CommunityPost::STATUS_HIDDEN,
            ]),
        ]);

        $post->update(['status' => $validated['status']]);

        return back()->with('success', "Cập nhật trạng thái bài viết '{$post->title}' thành công.");
    }

    /**
     * Xóa vĩnh viễn bài đăng vi phạm
     * DELETE /admin/community/{post}
     */
    public function destroy(CommunityPost $post)
    {
        $title = $post->title;
        $post->products()->detach(); // Xóa quan hệ tag
        $post->delete();

        return back()->with('success', "Đã xóa bài đăng '{$title}'.");
    }
}