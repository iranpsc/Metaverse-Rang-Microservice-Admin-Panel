
@props([
    'href' => '#',
    'active' => false,
    'title' => ''
])

<li>
    @if ($active)
        {{ $title }}
    @else
        <a href="{{ $href }}">{{ $title }}</a>
    @endif
</li>
