@props(['label', 'name', 'type' => 'text'])

<div class="form-group row">
    <label for="{{ $name }}" class="form-col-label col-sm-4">{{ $label }}</label>
    <div class="col-sm-8">
        <input type="{{ $type }}" name="{{ $name }}" class="form-control rounded"
            wire:model="{{ $name }}" {{ $attributes }}>
        @error($name)
            <span class="text-danger">{{ $message }}</span>
        @enderror
        @if($type === 'file')
            <div class="form-text text-success" wire:loading wire:target="{{ $name }}">در حال بارگذاری ...</div>
        @endif
    </div>
</div>
