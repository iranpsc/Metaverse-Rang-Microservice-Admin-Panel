<div>
    <x-modals.modal id="edit-field-{{ $field->id }}" title="ویرایش عبارت">
        <x-forms.group for="name" label="نام عبارت">
            <x-forms.input wire:model.defer="name" id="name" />
            @error('name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="translation" label="ترجمه">
            <x-forms.input wire:model.defer="translation" id="translation" />
            @error('translation')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-slot name="footer">
            <x-buttons.btn-success wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>
</div>
