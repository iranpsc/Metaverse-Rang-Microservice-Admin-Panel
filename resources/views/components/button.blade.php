@props([
    'id' => '',
    'color' => 'primary',
])

<button type="button" id="{{ $id }}" {{ $attributes->merge(['class' => "btn btn-$color rounded"]) }}  >
    {{ $slot }}
</button>
