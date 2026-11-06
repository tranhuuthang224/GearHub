<?php

namespace Database\Seeders;

use App\Models\CommunityPost;
use App\Models\CommunityPostComment;
use App\Models\CommunityPostLike;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CommunitySeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) {
            return;
        }

        $allProducts = Product::where('is_active', true)->get();

        $postsData = [
            [
                'title'       => 'Góc làm việc Tối giản trắng xám cho lập trình viên',
                'style'       => CommunityPost::STYLE_MINIMALIST,
                'description' => 'Setup theo triết lý Minimalism: giấu dây 100%, sử dụng màn hình cong và bàn phím cơ không dây để bàn luôn rộng rãi, tập trung tối đa khi code.',
                'image'       => 'https://images.unsplash.com/photo-1518455027359-f3f8164ba6bd?auto=format&fit=crop&w=1000&q=80',
                'likes'       => 142,
                'is_featured' => true,
            ],
            [
                'title'       => 'Cyberpunk 2077 Night City Battlestation - Full RGB Neon',
                'style'       => CommunityPost::STYLE_CYBERPUNK,
                'description' => 'Góc gaming ban đêm phong cách Cyberpunk với dải LED RGB đồng bộ Nanoleaf và phím cơ custom âm thanh cực êm.',
                'image'       => 'https://images.unsplash.com/photo-1616588589676-62b3bd4ff6d2?auto=format&fit=crop&w=1000&q=80',
                'likes'       => 289,
                'is_featured' => true,
            ],
            [
                'title'       => 'All-White Aesthetic: Không gian sáng tạo tinh khôi cho Designer',
                'style'       => CommunityPost::STYLE_WHITE,
                'description' => 'Tone trắng tinh tế kết hợp chút ánh sáng tự nhiên từ cửa sổ. Chuột và phím đều mua tại GearHub đợt sale vừa rồi siêu ưng ý!',
                'image'       => 'https://images.unsplash.com/photo-1593642702821-c8da6771f0c6?auto=format&fit=crop&w=1000&q=80',
                'likes'       => 195,
                'is_featured' => true,
            ],
            [
                'title'       => 'Góc Chill Bàn Gỗ Tự Nhiên (Warm Walnut) ấm cúng',
                'style'       => CommunityPost::STYLE_WOOD,
                'description' => 'Bàn làm việc mặt gỗ óc chó nguyên tấm, phối cùng keycap màu cafe sữa cổ điển. Ngồi gõ phím cả ngày không thấy mỏi mệt.',
                'image'       => 'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?auto=format&fit=crop&w=1000&q=80',
                'likes'       => 168,
                'is_featured' => false,
            ],
            [
                'title'       => 'Clean Desk Workspace: Tối giản phụ kiện, tối đa năng suất',
                'style'       => CommunityPost::STYLE_MINIMALIST,
                'description' => 'Chỉ giữ lại những gì cần thiết nhất: 1 laptop, 1 màn hình rời và bộ đôi phím chuột gaming siêu nhẹ.',
                'image'       => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?auto=format&fit=crop&w=1000&q=80',
                'likes'       => 93,
                'is_featured' => false,
            ],
            [
                'title'       => 'Triple Monitor RGB Battlestation đỉnh cao cày Rank FPS',
                'style'       => CommunityPost::STYLE_CYBERPUNK,
                'description' => 'Setup 3 màn hình xoay dọc + ngang, gear thi đấu chuyên nghiệp gồm chuột 8KHz và tai nghe 7.1 vòm chống ồn.',
                'image'       => 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=1000&q=80',
                'likes'       => 312,
                'is_featured' => true,
            ],
            [
                'title'       => 'Pastel & White Vibe: Góc học tập nhẹ nhàng chuẩn Hàn Quốc',
                'style'       => CommunityPost::STYLE_WHITE,
                'description' => 'Góc học tập và làm việc dịu mắt với gam màu trắng pastel, deskmat nỉ dày dặn và loa bluetooth retro.',
                'image'       => 'https://images.unsplash.com/photo-1581291518655-9523c932edcf?auto=format&fit=crop&w=1000&q=80',
                'likes'       => 210,
                'is_featured' => false,
            ],
            [
                'title'       => 'Gỗ Sồi & Đèn Vàng Vintage: Nơi thư giãn sau giờ làm việc',
                'style'       => CommunityPost::STYLE_WOOD,
                'description' => 'Không gian hoài niệm với đèn bàn Edison vàng ấm áp và phím cơ gõ lách cách thư thái.',
                'image'       => 'https://images.unsplash.com/photo-1542435503-956c469947f6?auto=format&fit=crop&w=1000&q=80',
                'likes'       => 125,
                'is_featured' => false,
            ],
            [
                'title'       => 'Minimalist Home Office phong cách Bắc Âu hiện đại',
                'style'       => CommunityPost::STYLE_MINIMALIST,
                'description' => 'Thiết kế mở nhiều ánh sáng ban ngày, bàn nâng hạ công thái học kết hợp ghế lưới thoáng khí.',
                'image'       => 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&w=1000&q=80',
                'likes'       => 177,
                'is_featured' => false,
            ],
            [
                'title'       => 'Neon Blue & Pink Battlestation: Trải nghiệm Gaming đắm chìm',
                'style'       => CommunityPost::STYLE_CYBERPUNK,
                'description' => 'Bộ gear phối 2 tông màu đối lập xanh neon và hồng magenta, cảm giác như lạc vào thế giới Tron Legacy.',
                'image'       => 'https://images.unsplash.com/photo-1598550476439-6847785fcea6?auto=format&fit=crop&w=1000&q=80',
                'likes'       => 254,
                'is_featured' => true,
            ],
        ];

        $sampleComments = [
            'Setup đẹp quá bác ơi! Bàn phím gõ tiếng có đầm không ạ?',
            'Góc máy gọn gàng mê mẩn thực sự, xin link giá treo tai nghe với ạ!',
            'Đèn LED dán phía sau màn hình dùng loại nào vậy bạn ơi?',
            'Tone màu này nhìn sang xịn mịn quá, 10/10!',
            'Con chuột GearHub kia cầm ôm tay không bạn? Đang tính xúc một em.',
        ];

        foreach ($postsData as $index => $item) {
            $user = $users->random();
            $slug = Str::slug($item['title']) . '-' . Str::random(5);

            $post = CommunityPost::create([
                'user_id'        => $user->id,
                'title'          => $item['title'],
                'slug'           => $slug,
                'description'    => $item['description'],
                'image'          => $item['image'],
                'style'          => $item['style'],
                'likes_count'    => $item['likes'],
                'comments_count' => rand(2, 5),
                'views_count'    => rand(300, 1500),
                'is_featured'    => $item['is_featured'],
            ]);

            // Gắn thẻ 2-4 sản phẩm ngẫu nhiên từ catalog
            if ($allProducts->isNotEmpty()) {
                $taggedCount = min(rand(2, 4), $allProducts->count());
                $taggedProducts = $allProducts->random($taggedCount);
                $post->products()->attach($taggedProducts->pluck('id'));
            }

            // Tạo like thực tế từ users
            $likerCount = min(rand(1, 3), $users->count());
            $likers = $users->random($likerCount);
            foreach ($likers as $liker) {
                CommunityPostLike::firstOrCreate([
                    'community_post_id' => $post->id,
                    'user_id'           => $liker->id,
                ]);
            }

            // Tạo comments
            $numComments = rand(2, 4);
            for ($c = 0; $c < $numComments; $c++) {
                CommunityPostComment::create([
                    'community_post_id' => $post->id,
                    'user_id'           => $users->random()->id,
                    'content'           => $sampleComments[array_rand($sampleComments)],
                    'created_at'        => now()->subHours(rand(1, 72)),
                ]);
            }
        }
    }
}
