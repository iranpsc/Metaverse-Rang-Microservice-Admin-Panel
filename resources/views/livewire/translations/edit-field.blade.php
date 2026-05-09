<div>
    <x-modal id="edit-field-{{ $field->id }}" title="ویرایش عبارت">
        <x-form.input name="translation" label="ترجمه" />
        <x-slot name="footer">
            <x-button wire:loading.attr="disabled" wire:click="save">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot>
    </x-modals.modal>
</div>
