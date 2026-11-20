<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function callback(Request $request): RedirectResponse
    {
        // Handle cancellation or OAuth error returned in query string
        if ($request->has('error') || !$request->has('code')) {
            return redirect()->route('login')->with('error', 'Đăng nhập bằng tài khoản Google đã bị hủy hoặc xảy ra lỗi.');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            Log::error('Google OAuth callback failed', [
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('login')->with('error', 'Không thể kết nối với tài khoản Google. Vui lòng thử lại sau.');
        }

        $email = $googleUser->getEmail();
        $googleId = $googleUser->getId();

        if (empty($email) || empty($googleId)) {
            return redirect()->route('login')->with('error', 'Không thể lấy thông tin email từ tài khoản Google.');
        }

        // 1. Check if user already exists by google_id
        $user = User::where('google_id', $googleId)->first();

        if (!$user) {
            // 2. Check if user exists by email -> link google_id
            $user = User::where('email', $email)->first();

            if ($user) {
                $user->google_id = $googleId;
                if (empty($user->avatar) && !empty($googleUser->getAvatar())) {
                    $user->avatar = $googleUser->getAvatar();
                }
                if (empty($user->email_verified_at)) {
                    $user->email_verified_at = now();
                }
                $user->save();
            } else {
                // 3. Create a brand new user
                $user = User::create([
                    'name'              => $googleUser->getName() ?: (explode('@', $email)[0] ?? 'User'),
                    'email'             => $email,
                    'google_id'         => $googleId,
                    'avatar'            => $googleUser->getAvatar(),
                    'password'          => Hash::make(Str::random(32)),
                    'phone'             => null,
                ]);

                // Ensure role and active status are explicitly set and verified
                $user->role = User::ROLE_CUSTOMER;
                $user->is_active = true;
                $user->email_verified_at = now();
                $user->save();
            }
        } else {
            // Update avatar if currently empty
            if (empty($user->avatar) && !empty($googleUser->getAvatar())) {
                $user->avatar = $googleUser->getAvatar();
                $user->save();
            }
        }

        // Check active status
        if (!$user->is_active) {
            return redirect()->route('login')->with('error', User::INACTIVE_MESSAGE);
        }

        // Authenticate the user with "remember me"
        Auth::login($user, remember: true);

        $request->session()->regenerate();

        $destination = $user->isAdmin()
            ? route('admin.dashboard', absolute: false)
            : route('home', absolute: false);

        return redirect()->intended($destination)->with('status', 'Đăng nhập bằng tài khoản Google thành công!');
    }
}
