<div>
    <x-modal id="edit-tab" title="ویرایش تب">
        <x-form.input name="editingTabName" label="نام تب" />
        <x-slot name="footer">
            <x-button wire:loading.attr="disabled" wire:click="updateTab">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot>
    </x-modal>
</div>
