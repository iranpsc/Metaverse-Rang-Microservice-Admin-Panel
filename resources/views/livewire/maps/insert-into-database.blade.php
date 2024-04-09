<div>
    <x-modal id="map-modal-{{ $map->id }}" title="وارد کردن اطلاعات نقشه به دیتابیس">
        <x-form.verification id="{{ $map->id }}" />

        <x-slot name="footer">
            <x-button wire:loading.attr="disabled" wire:click="insertIntoDatabase({{ $map->id }})">ثبت
                نهایی</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot>
    </x-modals.modal>
</div>
