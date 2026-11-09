<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_user_can_update_phone_and_avatar(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $user = User::factory()->create();
        $avatar = \Illuminate\Http\UploadedFile::fake()->image('my-avatar.png');

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name'   => 'Nguyen Van A',
                'email'  => $user->email,
                'phone'  => '0912345678',
                'avatar' => $avatar,
            ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/profile');

        $user->refresh();
        $this->assertSame('Nguyen Van A', $user->name);
        $this->assertSame('0912345678', $user->phone);
        $this->assertNotNull($user->avatar);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($user->avatar);
    }

    public function test_user_can_manage_shipping_addresses(): void
    {
        $user = User::factory()->create();

        // 1. Thêm địa chỉ mới
        $response = $this
            ->actingAs($user)
            ->post('/profile/addresses', [
                'receiver_name' => 'Tran Van B',
                'phone'         => '0987654321',
                'address_line'  => '123 Đường Nguyễn Huệ, Quận 1, TP.HCM',
                'is_default'    => 1,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('addresses', [
            'user_id'       => $user->id,
            'receiver_name' => 'Tran Van B',
            'is_default'    => 1,
        ]);

        $address1 = $user->addresses()->first();

        // 2. Thêm địa chỉ thứ 2
        $this->actingAs($user)->post('/profile/addresses', [
            'receiver_name' => 'Tran Van C',
            'phone'         => '0987654322',
            'address_line'  => '456 Đường Lê Lợi, Quận 1, TP.HCM',
            'is_default'    => 1,
        ]);

        $address2 = $user->addresses()->where('id', '!=', $address1->id)->first();
        $this->assertTrue($address2->fresh()->is_default);
        $this->assertFalse($address1->fresh()->is_default);

        // 3. Đặt lại địa chỉ 1 làm mặc định
        $this->actingAs($user)->post("/profile/addresses/{$address1->id}/default");
        $this->assertTrue($address1->fresh()->is_default);
        $this->assertFalse($address2->fresh()->is_default);

        // 4. Cập nhật địa chỉ 1
        $this->actingAs($user)->put("/profile/addresses/{$address1->id}", [
            'receiver_name' => 'Tran Van B (Updated)',
            'phone'         => '0987654321',
            'address_line'  => '123B Đường Nguyễn Huệ, Quận 1, TP.HCM',
            'is_default'    => 1,
        ]);
        $this->assertSame('Tran Van B (Updated)', $address1->fresh()->receiver_name);

        // 5. Xóa địa chỉ 2
        $this->actingAs($user)->delete("/profile/addresses/{$address2->id}");
        $this->assertDatabaseMissing('addresses', ['id' => $address2->id]);
    }

    public function test_user_cannot_manage_other_users_address(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $address = \App\Models\Address::create([
            'user_id'       => $user1->id,
            'receiver_name' => 'User 1',
            'phone'         => '0912345678',
            'address_line'  => 'Dia chi 1 khu vuc ABC',
            'is_default'    => 1,
        ]);

        $response = $this
            ->actingAs($user2)
            ->delete("/profile/addresses/{$address->id}");

        $response->assertForbidden();
    }

    public function test_user_can_delete_own_community_post(): void
    {
        $user = User::factory()->create();
        $post = \App\Models\CommunityPost::create([
            'user_id'     => $user->id,
            'title'       => 'My Test Setup Post',
            'slug'        => 'my-test-setup-post-123',
            'image'       => 'https://example.com/setup.jpg',
            'style'       => 'minimalist',
            'status'      => 'published',
            'description' => 'A clean desk setup',
        ]);

        $response = $this
            ->actingAs($user)
            ->delete("/profile/community-posts/{$post->id}");

        $response->assertRedirect('/profile?tab=posts');
        $this->assertDatabaseMissing('community_posts', ['id' => $post->id]);
    }

    public function test_user_cannot_delete_other_user_community_post(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $post = \App\Models\CommunityPost::create([
            'user_id'     => $user1->id,
            'title'       => 'User 1 Setup Post',
            'slug'        => 'user-1-setup-post-456',
            'image'       => 'https://example.com/setup.jpg',
            'style'       => 'cyberpunk_rgb',
            'status'      => 'published',
            'description' => 'A cyberpunk battlestation',
        ]);

        $response = $this
            ->actingAs($user2)
            ->delete("/profile/community-posts/{$post->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('community_posts', ['id' => $post->id]);
    }
}
