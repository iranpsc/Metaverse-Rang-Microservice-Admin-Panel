<div>
    <x-modals.modal id="update-map-modal-{{ $map->id }}" title="بروزرسانی نقشه">

        <x-forms.group for="pointFile-{{ $map->id }}" label="بارگذاری فایل نقطه مرکزی">
            <x-forms.input type="file" wire:model="pointFile" id="pointFile-{{ $map->id }}" />
            <x-progress-bar wire:loading wire:target="pointFile" />
            @error('pointFile')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="borderFile-{{ $map->id }}" label="بارگذاری فایل مرز">
            <x-forms.input type="file" wire:model="borderFile" id="borderFile-{{ $map->id }}" />
            <x-progress-bar wire:loading wire:target="borderFile" />
            @error('borderFile')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="color" label="رنگ محدوده">
            <x-forms.input type="color" wire:model="color" id="color" />
            @error('color')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.verification/>

        <x-slot name="footer">
            <x-buttons.btn-success wire:loading.attr="disabled" wire:click="save">بارگذاری</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>
</div>
