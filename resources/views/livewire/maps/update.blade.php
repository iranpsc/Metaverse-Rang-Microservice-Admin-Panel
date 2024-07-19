<div>
    <x-modal id="update-map-modal-{{ $map->id }}" title="بروزرسانی نقشه">

        <x-form.input name="name" label="نام آبادی" />

        <x-form.input type="file" name="pointFile" label="بارگذاری فایل نقطه مرکزی" />

        <x-form.input type="file" name="borderFile" label="بارگذاری فایل مرز" />

        <x-form.input type="color" name="color" label="رنگ محدوده" />

        @production
            <x-form.verification/>
        @endproduction

        <x-slot name="footer">
            <x-button wire:loading.attr="disabled" wire:click="save">بارگذاری</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot>
    </x-modals.modal>
</div>
