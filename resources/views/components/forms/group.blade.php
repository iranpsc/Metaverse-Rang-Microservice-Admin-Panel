@props(['label', 'for'])

<div {{$attributes->merge(['class'=>"form-group row"])}}>
    <label for="{{ $for }}" {{ $attributes->merge(['class'=>"form-col-label col-sm-4"]) }}>{{ $label }}</label>
    <div class="col-sm-8">
        {{ $slot }}
    </div>
</div>
