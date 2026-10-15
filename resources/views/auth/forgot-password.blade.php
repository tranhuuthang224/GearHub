<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">Forgot password?</h2>
        <p class="text-sm text-gray-500 mt-1.5 leading-relaxed">
            No worries! Enter your email address and we'll send you a link to reset your password.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
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
                :error="$errors->first('email')"
            />
        </div>

        <div>
            <x-button type="submit" class="w-full py-3 rounded-xl shadow-md font-bold text-base" variant="primary">
                Email Password Reset Link
            </x-button>
        </div>

        <p class="text-center text-sm text-gray-500 pt-2">
            Remember your password? 
            <a href="{{ route('login') }}" class="font-bold text-primary hover:text-primary-hover transition">
                Back to login
            </a>
        </p>
    </form>
</x-guest-layout>
