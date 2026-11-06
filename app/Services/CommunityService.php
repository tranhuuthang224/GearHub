<?php

namespace App\Services;

use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class CommunityService
{
    /**
     * Tạo bài viết khoe góc máy mới
     */
    public function createPost(User $user, array $data, UploadedFile $image, array $productIds = []): CommunityPost
    {
        // 1. Lưu file ảnh vào storage/community
        $path = $image->store('community', 'public');

        $desc = $data['content'] ?? $data['description'] ?? null;

        // 2. Tạo bài viết
        $post = CommunityPost::create([
            'user_id'     => $user->id,
            'title'       => $data['title'],
            'slug'        => Str::slug($data['title']) . '-' . Str::random(6),
            'content'     => $desc,
            'description' => $desc,
            'image'       => $path,
            'style'       => $data['style'] ?? 'minimalist',
            'status'      => CommunityPost::STATUS_PUBLISHED,
        ]);

        // 3. Gắn thẻ các sản phẩm (Tag products)
        if (!empty($productIds)) {
            $post->products()->sync($productIds);
        }

        return $post;
    }
}
