<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_redirects_to_google_oauth_provider(): void
    {
        $response = $this->get(route('auth.google.redirect'));

        $response->assertStatus(302);
        $this->assertStringContainsString('accounts.google.com', $response->headers->get('Location'));
    }

    public function test_creates_new_user_and_authenticates_via_google(): void
    {
        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-uid-1001');
        $abstractUser->shouldReceive('getName')->andReturn('Le Van Google');
        $abstractUser->shouldReceive('getEmail')->andReturn('levangoogle@gmail.com');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/avatar1001.jpg');

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->once()->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $response = $this->get(route('auth.google.callback', ['code' => 'mock-auth-code']));

        $response->assertRedirect(route('home', absolute: false));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'levangoogle@gmail.com',
            'google_id' => 'google-uid-1001',
            'role' => User::ROLE_CUSTOMER,
            'is_active' => true,
        ]);

        $createdUser = User::where('email', 'levangoogle@gmail.com')->first();
        $this->assertNotNull($createdUser->email_verified_at);
        $this->assertEquals('https://lh3.googleusercontent.com/avatar1001.jpg', $createdUser->avatar);
    }

    public function test_links_google_id_when_email_already_registered(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'existing@gmail.com',
            'google_id' => null,
            'avatar' => null,
        ]);

        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-uid-2002');
        $abstractUser->shouldReceive('getName')->andReturn('Existing User');
        $abstractUser->shouldReceive('getEmail')->andReturn('existing@gmail.com');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/avatar2002.jpg');

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->once()->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $response = $this->get(route('auth.google.callback', ['code' => 'mock-auth-code']));

        $response->assertRedirect(route('home', absolute: false));
        $this->assertAuthenticatedAs($existingUser);

        $this->assertDatabaseHas('users', [
            'id' => $existingUser->id,
            'email' => 'existing@gmail.com',
            'google_id' => 'google-uid-2002',
            'avatar' => 'https://lh3.googleusercontent.com/avatar2002.jpg',
        ]);
    }

    public function test_authenticates_existing_google_user(): void
    {
        $user = User::factory()->create([
            'email' => 'logged_in@gmail.com',
            'google_id' => 'google-uid-3003',
        ]);

        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-uid-3003');
        $abstractUser->shouldReceive('getName')->andReturn('Logged In User');
        $abstractUser->shouldReceive('getEmail')->andReturn('logged_in@gmail.com');
        $abstractUser->shouldReceive('getAvatar')->andReturn(null);

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->once()->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $response = $this->get(route('auth.google.callback', ['code' => 'mock-auth-code']));

        $response->assertRedirect(route('home', absolute: false));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_redirects_to_admin_dashboard_after_google_login(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@gearhub.com',
            'role' => User::ROLE_ADMIN,
            'google_id' => 'google-admin-4004',
        ]);

        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-admin-4004');
        $abstractUser->shouldReceive('getName')->andReturn('Admin GearHub');
        $abstractUser->shouldReceive('getEmail')->andReturn('admin@gearhub.com');
        $abstractUser->shouldReceive('getAvatar')->andReturn(null);

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->once()->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $response = $this->get(route('auth.google.callback', ['code' => 'mock-auth-code']));

        $response->assertRedirect(route('admin.dashboard', absolute: false));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_inactive_user_cannot_login_via_google(): void
    {
        $inactiveUser = User::factory()->create([
            'email' => 'banned@gmail.com',
            'is_active' => false,
            'google_id' => 'google-uid-5005',
        ]);

        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-uid-5005');
        $abstractUser->shouldReceive('getName')->andReturn('Banned User');
        $abstractUser->shouldReceive('getEmail')->andReturn('banned@gmail.com');
        $abstractUser->shouldReceive('getAvatar')->andReturn(null);

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->once()->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $response = $this->get(route('auth.google.callback', ['code' => 'mock-auth-code']));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', User::INACTIVE_MESSAGE);
        $this->assertGuest();
    }

    public function test_handles_cancellation_when_user_aborts_google_dialog(): void
    {
        $response = $this->get(route('auth.google.callback', ['error' => 'access_denied']));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_handles_socialite_exception_gracefully(): void
    {
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->once()->andThrow(new \Exception('OAuth token expired'));

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $response = $this->get(route('auth.google.callback', ['code' => 'invalid-token-code']));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Không thể kết nối với tài khoản Google. Vui lòng thử lại sau.');
        $this->assertGuest();
    }
}
