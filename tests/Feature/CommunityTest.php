<?php

namespace Tests\Feature;

use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_community_posts_list(): void
    {
        $user = User::factory()->create();
        CommunityPost::create([
            'user_id' => $user->id,
            'title'   => 'Góc setup tối giản 2026',
            'slug'    => 'goc-setup-toi-gian-2026',
            'image'   => 'community/test.jpg',
            'style'   => 'minimalist',
            'status'  => CommunityPost::STATUS_PUBLISHED,
        ]);

        $response = $this->get(route('community.index'));

        $response->assertOk();
    }

    public function test_authenticated_user_can_toggle_like(): void
    {
        $author = User::factory()->create();
        $liker  = User::factory()->create();

        $post = CommunityPost::create([
            'user_id'     => $author->id,
            'title'       => 'Setup RGB cực chất',
            'slug'        => 'setup-rgb-cuc-chat',
            'image'       => 'community/rgb.jpg',
            'style'       => 'rgb',
            'status'      => CommunityPost::STATUS_PUBLISHED,
            'likes_count' => 0,
        ]);

        // Thả tim lần 1 -> likes_count = 1
        $response1 = $this->actingAs($liker)->postJson(route('community.like', $post->id));
        $response1->assertOk()->assertJson(['liked' => true, 'likes_count' => 1]);
        $this->assertEquals(1, $post->fresh()->likes_count);

        // Bấm lần 2 -> bỏ tim -> likes_count = 0
        $response2 = $this->actingAs($liker)->postJson(route('community.like', $post->id));
        $response2->assertOk()->assertJson(['liked' => false, 'likes_count' => 0]);
        $this->assertEquals(0, $post->fresh()->likes_count);
    }

    public function test_authenticated_user_can_comment_on_post(): void
    {
        $author = User::factory()->create();
        $reader = User::factory()->create();

        $post = CommunityPost::create([
            'user_id' => $author->id,
            'title'   => 'Bàn làm việc màu trắng',
            'slug'    => 'ban-lam-viec-mau-trang',
            'image'   => 'community/white.jpg',
            'style'   => 'white_aesthetic',
            'status'  => CommunityPost::STATUS_PUBLISHED,
        ]);

        $response = $this->actingAs($reader)->post(route('community.comment', $post->id), [
            'content' => 'Góc làm việc nhìn chill quá bạn ơi!',
        ]);

        $this->assertDatabaseHas('post_comments', [
            'community_post_id' => $post->id,
            'user_id'           => $reader->id,
            'content'           => 'Góc làm việc nhìn chill quá bạn ơi!',
        ]);

        $this->assertEquals(1, $post->fresh()->comments_count);
    }
}