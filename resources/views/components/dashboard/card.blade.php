@props([
    'type',
    'value',
])

<div class="stat-box bg-{{$type}} shadow">
    <a href="#">
        <div class="stat">
            <div class="counter-down" data-value="{{ $value }}"></div>
            <div class="h3">{{ $slot }}</div>
        </div><!-- /.stat -->
        <div class="visual">
            <i class="icon-people"></i>
        </div><!-- /.visual -->
    </a>
</div><!-- /.stat-box -->
