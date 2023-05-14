@props(['link', 'target' => '_self'])

<a href="{{ $link }}" target="{{ $target }}" {{ $attributes->merge(['class' => "btn btn-link rounded"])}}>{{ $slot }}</a>
