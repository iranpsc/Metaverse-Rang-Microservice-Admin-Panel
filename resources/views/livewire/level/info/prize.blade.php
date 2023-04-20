<div class="text-right">
    <div class="container my-2">
        @if (session()->has('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        @if (session()->has('error'))
            <x-alerts.danger>{{ session('error') }}</x-alerts.danger>
        @endif
    </div>

    <div class="row">
        <div class="col-md-6">
            <x-forms.group for="psc-{{ $level->id }}" label="دریافت PSC">
                <x-forms.input id="psc-{{ $level->id }}" wire:model="psc" />
                @error('psc')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>
            <x-forms.group for="blue-{{ $level->id }}" label="دریافت رنگ آبی">
                <x-forms.input id="blue-{{ $level->id }}" wire:model="blue" />
                @error('blue')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>
            <x-forms.group for="red-{{ $level->id }}" label="دریافت رنگ قرمز">
                <x-forms.input id="red-{{ $level->id }}" wire:model="red" />
                @error('red')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>
            <x-forms.group for="yellow-{{ $level->id }}" label="دریافت رنگ زرد">
                <x-forms.input id="yellow-{{ $level->id }}" wire:model="yellow" />
                @error('yellow')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>
        </div>
        <div class="col-md-6">
            <x-forms.group for="satisfaction-{{ $level->id }}" label="واحد رضایت">
                <x-forms.input id="satisfaction-{{ $level->id }}" wire:model="satisfaction" />
                @error('satisfaction')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="effect-{{ $level->id }}" label="دریافت حدتاثیر">
                <x-forms.input id="effect-{{ $level->id }}" wire:model="effect" />
                @error('effect')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>
        </div>
    </div>
    <hr>
    <x-forms.verification id="{{ $level->id }}"/>
    <hr>
    <x-buttons.btn-primary class="w-25" wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-primary>
</div>
