<div>
    {{-- Knowing others is intelligence; knowing yourself is true wisdom. --}}
    <x-modals.modal id="map-modal-{{ $map->id }}" title="وارد کردن اطلاعات نقشه به دیتابیس">
        <x-forms.verification id="{{ $map->id }}" />

        <x-slot name="footer">
            <x-buttons.btn-primary wire:loading.attr="disabled" wire:click="insertIntoDatabase({{ $map->id }})">ثبت
                نهایی</x-buttons.btn-primary>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>
</div>
