@props([
    'label' => null,
    'name' => '',
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'hint' => null,
])

<div class="space-y-1.5">
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-semibold text-editorial-heading">
            {{ $label }}
            @if ($required)
                <span class="text-[#B99A68]" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'w-full rounded-xl px-3.5 py-2.5 text-sm input-editorial transition-all']) }}
    >

    @if ($hint)
        <p class="text-xs text-editorial-secondary">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="text-xs font-medium text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
    @enderror
</div>