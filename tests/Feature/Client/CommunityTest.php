<?php

namespace Tests\Feature\Client;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CommunityPost;
use App\Models\CommunityPostComment;
use App\Models\CommunityPostLike;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $product1;
    private Product $product2;
    private CommunityPost $minimalistPost;
    private CommunityPost $cyberpunkPost;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name'  => 'Nguyễn Hoàng Gamer',
            'email' => 'gamer@gearhub.vn',
        ]);

        $category = Category::create([
            'name'      => 'Bàn phím cơ',
            'slug'      => 'ban-phim-co',
            'parent_id' => null,
        ]);

        $brand = Brand::create([
            'name' => 'Aula',
            'slug' => 'aula',
        ]);

        $this->product1 = Product::create([
            'category_id'    => $category->id,
            'brand_id'       => $brand->id,
            'name'           => 'Bàn phím Aula F75',
            'slug'           => 'ban-phim-aula-f75',
            'sku'            => 'KB-AULA-F75',
            'price'          => 1200000,
            'sale_price'     => 990000,
            'stock_quantity' => 10,
            'is_active'      => true,
        ]);

        $this->product2 = Product::create([
            'category_id'    => $category->id,
            'brand_id'       => $brand->id,
            'name'           => 'Chuột Aula SC580',
            'slug'           => 'chuot-aula-sc580',
            'sku'            => 'MO-AULA-SC580',
            'price'          => 550000,
            'stock_quantity' => 15,
            'is_active'      => true,
        ]);

        $this->minimalistPost = CommunityPost::create([
            'user_id'        => $this->user->id,
            'title'          => 'Góc làm việc Minimalist thanh lịch',
            'slug'           => 'goc-lam-viec-minimalist-thanh-lich',
            'description'    => 'Setup gọn gàng trắng xám không một cọng dây thừa.',
            'image'          => 'https://images.unsplash.com/photo-1518455027359-f3f8164ba6bd',
            'style'          => CommunityPost::STYLE_MINIMALIST,
            'likes_count'    => 10,
            'comments_count' => 1,
            'is_featured'    => true,
        ]);
        $this->minimalistPost->products()->attach([$this->product1->id, $this->product2->id]);

        $this->cyberpunkPost = CommunityPost::create([
            'user_id'        => $this->user->id,
            'title'          => 'Battlestation Cyberpunk RGB rực rỡ ban đêm',
            'slug'           => 'battlestation-cyberpunk-rgb-ruc-ro-ban-dem',
            'description'    => 'Full đèn LED neon tím hồng phong cách tương lai.',
            'image'          => 'https://images.unsplash.com/photo-1616588589676-62b3bd4ff6d2',
            'style'          => CommunityPost::STYLE_CYBERPUNK,
            'likes_count'    => 25,
            'comments_count' => 0,
            'is_featured'    => false,
        ]);
    }

    public function test_community_index_page_can_be_rendered(): void
    {
        $response = $this->get(route('community.index'));

        $response->assertOk();
        $response->assertSee('Diễn đàn Khoe Góc Máy');
        $response->assertSee('Tất cả phong cách');
        $response->assertSee('Góc làm việc Minimalist thanh lịch');
        $response->assertSee('Battlestation Cyberpunk RGB rực rỡ ban đêm');
        $response->assertSee('2 gear GearHub');
    }

    public function test_community_can_be_filtered_by_style(): void
    {
        $response = $this->get(route('community.index', ['style' => CommunityPost::STYLE_MINIMALIST]));

        $response->assertOk();
        $response->assertSee('Góc làm việc Minimalist thanh lịch');
        $response->assertDontSee('Battlestation Cyberpunk RGB rực rỡ ban đêm');
    }

    public function test_community_can_be_searched_by_keyword(): void
    {
        $response = $this->get(route('community.index', ['search' => 'Cyberpunk']));

        $response->assertOk();
        $response->assertSee('Battlestation Cyberpunk RGB rực rỡ ban đêm');
        $response->assertDontSee('Góc làm việc Minimalist thanh lịch');
    }

    public function test_community_post_json_detail_can_be_retrieved(): void
    {
        $response = $this->getJson(route('community.show', $this->minimalistPost));

        $response->assertOk();
        $response->assertJson([
            'id'    => $this->minimalistPost->id,
            'title' => 'Góc làm việc Minimalist thanh lịch',
            'style' => CommunityPost::STYLE_MINIMALIST,
        ]);
        $response->assertJsonCount(2, 'products');
    }

    public function test_guest_cannot_like_post(): void
    {
        $response = $this->postJson(route('community.like', $this->minimalistPost));

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_authenticated_user_can_toggle_like_on_post(): void
    {
        $initialLikes = $this->minimalistPost->likes_count;

        // Thả tim lần đầu
        $response = $this->actingAs($this->user)
            ->postJson(route('community.like', $this->minimalistPost));

        $response->assertOk();
        $response->assertJson([
            'success'     => true,
            'liked'       => true,
            'likes_count' => $initialLikes + 1,
        ]);

        $this->assertDatabaseHas('community_post_likes', [
            'community_post_id' => $this->minimalistPost->id,
            'user_id'           => $this->user->id,
        ]);

        // Thả tim lần 2 (bỏ tim)
        $response2 = $this->actingAs($this->user)
            ->postJson(route('community.like', $this->minimalistPost));

        $response2->assertOk();
        $response2->assertJson([
            'success'     => true,
            'liked'       => false,
            'likes_count' => $initialLikes,
        ]);

        $this->assertDatabaseMissing('community_post_likes', [
            'community_post_id' => $this->minimalistPost->id,
            'user_id'           => $this->user->id,
        ]);
    }

    public function test_authenticated_user_can_comment_on_post(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('community.comment', $this->minimalistPost), [
                'content' => 'Góc máy đẹp quá bạn ơi!',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success'        => true,
            'comments_count' => 2,
        ]);

        $this->assertDatabaseHas('community_post_comments', [
            'community_post_id' => $this->minimalistPost->id,
            'user_id'           => $this->user->id,
            'content'           => 'Góc máy đẹp quá bạn ơi!',
        ]);
    }
}
