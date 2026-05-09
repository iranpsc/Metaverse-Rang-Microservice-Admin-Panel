@props([
    'color' => 'primary',
    'size' => 'sm',
    'type' => 'button',
])

<button type="{{ $type }}" {{ $attributes->merge(['class' => "btn btn-$color btn-$size rounded"]) }}  >
    {{ $slot }}
</button>
