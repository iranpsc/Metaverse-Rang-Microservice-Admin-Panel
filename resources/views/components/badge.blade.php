@props(['type' => 'gray'])

<span {{ $attributes->merge(['class' => "badge bg-$type"]) }}>
    {{ $slot }}
</span>