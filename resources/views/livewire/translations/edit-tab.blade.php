<div>
    <x-modal id="edit-tab-{{ $tab->id }}" title="ویرایش تب">
        <x-form.input name="name" label="نام تب" />
        <x-slot name="footer">
            <x-button wire:loading.attr="disabled" wire:click="update">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot>
    </x-modal>
</div>
