@props([
    'label',
    'name',
    'type' => 'text',
    'value' => '',
    'required' => false,
    'disabled' => false,
    'error' => null,
    'placeholder' => '',
])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium leading-6 text-gray-900">
        {{ $label }}
        @if($required)
            <span class="text-red-500">*</span>
        @endif
    </label>
    <div class="mt-2">
        @if($type === 'textarea')
            <textarea
                name="{{ $name }}"
                id="{{ $name }}"
                {{ $disabled ? 'disabled' : '' }}
                {{ $required ? 'required' : '' }}
                {{ $attributes->merge([
                    'class' => 'block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ' . 
                              ($error ? 'ring-red-300 focus:ring-red-500' : 'ring-gray-300 focus:ring-indigo-600') .
                              ' placeholder:text-gray-400 focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6'
                ]) }}
                placeholder="{{ $placeholder }}"
            >{{ $value }}</textarea>
        @elseif($type === 'select')
            <select
                name="{{ $name }}"
                id="{{ $name }}"
                {{ $disabled ? 'disabled' : '' }}
                {{ $required ? 'required' : '' }}
                {{ $attributes->merge([
                    'class' => 'block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ' . 
                              ($error ? 'ring-red-300 focus:ring-red-500' : 'ring-gray-300 focus:ring-indigo-600') .
                              ' focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6'
                ]) }}
            >
                {{ $slot }}
            </select>
        @else
            <input
                type="{{ $type }}"
                name="{{ $name }}"
                id="{{ $name }}"
                value="{{ $value }}"
                {{ $disabled ? 'disabled' : '' }}
                {{ $required ? 'required' : '' }}
                {{ $attributes->merge([
                    'class' => 'block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ' . 
                              ($error ? 'ring-red-300 focus:ring-red-500' : 'ring-gray-300 focus:ring-indigo-600') .
                              ' placeholder:text-gray-400 focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6'
                ]) }}
                placeholder="{{ $placeholder }}"
            >
        @endif

        @if($error)
            <p class="mt-2 text-sm text-red-600">{{ $error }}</p>
        @endif
    </div>
</div>
