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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        $response = $this->getJson(route('community.show', $this->minimalistPost->slug));

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
        $response = $this->postJson(route('community.like', $this->minimalistPost->slug));

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
            ->postJson(route('community.like', $this->minimalistPost->slug));

        $response->assertOk();
        $response->assertJson([
            'success'     => true,
            'liked'       => true,
            'likes_count' => $initialLikes + 1,
        ]);

        $this->assertDatabaseHas('post_likes', [
            'community_post_id' => $this->minimalistPost->id,
            'user_id'           => $this->user->id,
        ]);

        // Thả tim lần 2 (bỏ tim)
        $response2 = $this->actingAs($this->user)
            ->postJson(route('community.like', $this->minimalistPost->slug));

        $response2->assertOk();
        $response2->assertJson([
            'success'     => true,
            'liked'       => false,
            'likes_count' => $initialLikes,
        ]);

        $this->assertDatabaseMissing('post_likes', [
            'community_post_id' => $this->minimalistPost->id,
            'user_id'           => $this->user->id,
        ]);
    }

    public function test_authenticated_user_can_comment_on_post(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('community.comment', $this->minimalistPost->slug), [
                'content' => 'Góc máy đẹp quá bạn ơi!',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success'        => true,
            'comments_count' => 2,
        ]);

        $this->assertDatabaseHas('post_comments', [
            'community_post_id' => $this->minimalistPost->id,
            'user_id'           => $this->user->id,
            'content'           => 'Góc máy đẹp quá bạn ơi!',
        ]);
    }

    public function test_guest_redirected_to_login_when_accessing_create_page(): void
    {
        $response = $this->get(route('community.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_create_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('community.create'));

        $response->assertOk();
        $response->assertSee('Khoe Góc Máy Lên GearHub');
        $response->assertSee('Gắn thẻ (Tag) sản phẩm GearHub');
    }

    public function test_can_search_products_for_tagging(): void
    {
        $response = $this->getJson(route('community.search-products', ['q' => 'Aula F75']));

        $response->assertOk();
        $response->assertJsonFragment([
            'name' => 'Bàn phím Aula F75',
        ]);
    }

    public function test_authenticated_user_can_store_post_with_image_and_tagged_gears(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('my-battlestation.jpg', 1920, 1080);

        $response = $this->actingAs($this->user)->post(route('community.store'), [
            'title'       => 'Setup Bàn Gỗ Tự Nhiên Siêu Chill',
            'style'       => CommunityPost::STYLE_WOOD,
            'description' => 'Mặt bàn gỗ sồi tự nhiên phối cùng phím cơ âm trầm đục.',
            'image'       => $file,
            'product_ids' => [$this->product1->id, $this->product2->id],
        ]);

        $post = CommunityPost::where('title', 'Setup Bàn Gỗ Tự Nhiên Siêu Chill')->first();
        $this->assertNotNull($post);

        $response->assertRedirect(route('community.show', $post->slug));
        Storage::disk('public')->assertExists($post->image);

        $this->assertCount(2, $post->products);
    }

    public function test_post_detail_page_renders_with_sidebar_gears_and_buy_now_buttons(): void
    {
        $response = $this->get(route('community.show', $this->minimalistPost->slug));

        $response->assertOk();
        $response->assertSee($this->minimalistPost->title);
        $response->assertSee('Gear có trong ảnh');
        $response->assertSee('Bàn phím Aula F75');
        $response->assertSee('Chuột Aula SC580');
        $response->assertSee('Mua ngay');
        $response->assertSee('Thảo luận');
    }

    public function test_pending_post_is_hidden_from_index_and_direct_url_for_strangers(): void
    {
        $pendingPost = CommunityPost::create([
            'user_id'     => $this->user->id,
            'title'       => 'Bài viết đang chờ ban quản trị duyệt',
            'slug'        => 'bai-viet-dang-cho-duyet',
            'style'       => CommunityPost::STYLE_MINIMALIST,
            'image'       => 'https://images.unsplash.com/photo-1518455027359-f3f8164ba6bd',
            'status'      => CommunityPost::STATUS_PENDING,
        ]);

        // 1. Không xuất hiện trên trang chủ diễn đàn
        $indexRes = $this->get(route('community.index'));
        $indexRes->assertOk();
        $indexRes->assertDontSee('Bài viết đang chờ ban quản trị duyệt');

        // 2. Khách vãng lai truy cập trực tiếp bằng URL bị 404
        $directRes = $this->get(route('community.show', $pendingPost->slug));
        $directRes->assertNotFound();

        // 3. Chính tác giả bài viết đăng nhập thì xem trước được
        $authorRes = $this->actingAs($this->user)->get(route('community.show', $pendingPost->slug));
        $authorRes->assertOk();
        $authorRes->assertSee('Bài viết đang chờ ban quản trị duyệt');
    }
}
