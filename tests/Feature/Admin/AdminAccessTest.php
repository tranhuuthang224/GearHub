<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $email, string $role, bool $active = true): User
    {
        // role và is_active không nằm trong $fillable nên phải bỏ qua bảo vệ có chủ đích
        return User::unguarded(fn () => User::create([
            'name' => 'Test', 'email' => $email, 'password' => 'password123',
            'role' => $role, 'is_active' => $active,
        ]));
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect(route('login'));
        $this->get(route('admin.products.index'))->assertRedirect(route('login'));
    }

    public function test_customer_gets_403_in_admin_area(): void
    {
        $customer = $this->makeUser('khach@test.vn', User::ROLE_CUSTOMER);

        $this->actingAs($customer)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($customer)->get(route('admin.brands.index'))->assertForbidden();
        $this->actingAs($customer)->get(route('admin.categories.index'))->assertForbidden();
    }

    public function test_admin_can_open_dashboard_and_root_redirects_there(): void
    {
        $admin = $this->makeUser('admin@test.vn', User::ROLE_ADMIN);

        $this->actingAs($admin)->get('/admin/dashboard')->assertOk()->assertSee('Dashboard');
        $this->actingAs($admin)->get('/admin')->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_login_lands_on_admin_dashboard(): void
    {
        $this->makeUser('admin@test.vn', User::ROLE_ADMIN);

        $this->post(route('login'), ['email' => 'admin@test.vn', 'password' => 'password123'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_deactivated_account_cannot_log_in(): void
    {
        $this->makeUser('khoa@test.vn', User::ROLE_CUSTOMER, active: false);

        $this->post(route('login'), ['email' => 'khoa@test.vn', 'password' => 'password123'])
            ->assertSessionHasErrors(['email' => User::INACTIVE_MESSAGE]);

        $this->assertGuest();
    }

    public function test_wrong_password_does_not_reveal_that_an_account_is_deactivated(): void
    {
        $this->makeUser('khoa@test.vn', User::ROLE_CUSTOMER, active: false);

        $this->post(route('login'), ['email' => 'khoa@test.vn', 'password' => 'sai-mat-khau'])
            ->assertSessionHasErrors('email');

        $this->assertNotSame(User::INACTIVE_MESSAGE, session('errors')->first('email'));
        $this->assertGuest();
    }
}