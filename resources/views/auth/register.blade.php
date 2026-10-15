<x-guest-layout>
    <!-- Header Title (Figma Page 8) -->
    <div class="mb-6">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">Create account</h2>
        <p class="text-sm text-gray-500 mt-1">Join us to start shopping</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Full Name -->
        <div>
            <x-input
                label="Full name"
                id="name"
                type="text"
                name="name"
                :value="old('name')"
                placeholder="Nguyen Van A"
                required
                autofocus
                autocomplete="name"
                :error="$errors->first('name')"
            />
        </div>

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
                autocomplete="username"
                :error="$errors->first('email')"
            />
        </div>

        <!-- Phone Number -->
        <div>
            <x-input
                label="Phone"
                id="phone"
                type="tel"
                name="phone"
                :value="old('phone')"
                placeholder="09xx xxx xxx"
                autocomplete="tel"
                :error="$errors->first('phone')"
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
                autocomplete="new-password"
                :error="$errors->first('password')"
            />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input
                label="Confirm password"
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                placeholder="••••••••"
                required
                autocomplete="new-password"
                :error="$errors->first('password_confirmation')"
            />
        </div>

        <!-- Terms Agreement Checkbox (Figma Page 8) -->
        <div class="pt-1">
            <label class="flex items-start gap-2 cursor-pointer select-none">
                <input 
                    type="checkbox" 
                    name="terms" 
                    required 
                    class="w-4 h-4 mt-0.5 rounded border-gray-300 text-primary focus:ring-primary/20 transition"
                >
                <span class="text-xs text-gray-600 leading-snug">
                    I agree to the <a href="#" class="font-semibold text-primary hover:underline">Terms & Privacy Policy</a>
                </span>
            </label>
        </div>

        <!-- Submit Button (Figma Primary Red) -->
        <div class="pt-2">
            <x-button type="submit" class="w-full py-3 rounded-xl shadow-md font-bold text-base" variant="primary">
                Create account
            </x-button>
        </div>

        <!-- Login Link -->
        <p class="text-center text-sm text-gray-500 pt-3">
            Already have an account? 
            <a href="{{ route('login') }}" class="font-bold text-primary hover:text-primary-hover transition">
                Login
            </a>
        </p>
    </form>
</x-guest-layout>
