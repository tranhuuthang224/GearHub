@props([
    'disabled' => false,
    'label' => null,
    'name' => null,
    'required' => false,
    'error' => null,
    'hint' => null,
    'value' => null,
    'placeholder' => null,
    'options' => [],
])

<div class="w-full">
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-semibold text-gray-700 mb-1.5">
            {{ $label }}
            @if ($required)
                <span class="text-primary">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <select
            id="{{ $name }}"
            name="{{ $name }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge([
                'class' => 'w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/20 transition disabled:bg-gray-100 disabled:text-gray-500 appearance-none pr-10 ' . ($error ? 'border-rose-500 focus:border-rose-500 focus:ring-rose-200' : '')
            ]) }}
        >
            @if ($placeholder)
                <option value="" {{ old($name, $value) === null || old($name, $value) === '' ? 'selected' : '' }}>
                    {{ $placeholder }}
                </option>
            @endif

            @if (!empty($options))
                @foreach ($options as $optValue => $optLabel)
                    <option value="{{ $optValue }}" {{ (string) old($name, $value) === (string) $optValue ? 'selected' : '' }}>
                        {{ $optLabel }}
                    </option>
                @endforeach
            @else
                {{ $slot }}
            @endif
        </select>

        <!-- Chevron Down Icon -->
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-gray-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </div>

    @if ($hint && !$error)
        <p class="mt-1 text-xs text-gray-500">{{ $hint }}</p>
    @endif

    @if ($error)
        <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1 font-medium">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            {{ $error }}
        </p>
    @endif
</div>
