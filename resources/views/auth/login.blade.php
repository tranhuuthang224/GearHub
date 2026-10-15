<x-guest-layout>
    <!-- Header Title (Figma Page 7) -->
    <div class="mb-8">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">Welcome back</h2>
        <p class="text-sm text-gray-500 mt-1.5">Login to continue</p>
    </div>

    <!-- Session Status / Alert -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input
                label="Email"
                id="email"
                type="email"
                name="email"
                :value="old('email')"
                placeholder="you@example.com"
                required
                autofocus
                autocomplete="username"
                :error="$errors->first('email')"
            />
        </div>

        <!-- Password -->
        <div>
            <x-input
                label="Password"
                id="password"
                type="password"
                name="password"
                placeholder="••••••••"
                required
                autocomplete="current-password"
                :error="$errors->first('password')"
            />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between text-sm">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer select-none">
                <input 
                    id="remember_me" 
                    type="checkbox" 
                    name="remember" 
                    class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary/20 transition"
                >
                <span class="text-gray-600 text-xs sm:text-sm">Remember me</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-xs sm:text-sm font-medium text-gray-600 hover:text-primary transition">
                    Forgot password?
                </a>
            @endif
        </div>

        <!-- Submit Button (Figma Primary Red) -->
        <div>
            <x-button type="submit" class="w-full py-3 rounded-xl shadow-md font-bold text-base" variant="primary">
                Login
            </x-button>
        </div>

        <!-- Or Divider -->
        <div class="relative my-6 flex items-center justify-center">
            <div class="border-t border-gray-200 w-full"></div>
            <span class="bg-white px-3 text-xs text-gray-400 font-medium uppercase absolute">or</span>
        </div>

        <!-- Google Login Button (Matching Figma Page 7) -->
        <div>
            <button 
                type="button" 
                onclick="alert('Tính năng đăng nhập Google đang được tích hợp')" 
                class="w-full inline-flex items-center justify-center gap-3 py-2.5 px-4 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-sm font-semibold transition"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                </svg>
                Continue with Google
            </button>
        </div>

        <!-- Register Link -->
        <p class="text-center text-sm text-gray-500 pt-2">
            Don't have an account? 
            <a href="{{ route('register') }}" class="font-bold text-primary hover:text-primary-hover transition">
                Register
            </a>
        </p>
    </form>
</x-guest-layout>
