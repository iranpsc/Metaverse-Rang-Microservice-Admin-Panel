<div>
    <x-modal id="edit-system-variable-{{ $variable->id }}" title="ویرایش متغیر">
        <x-form.input name="name" label="نام متغییر" />

        <x-form.input name="slug" label="اسلاگ" />

        <x-form.input name="value" label="مقدار" />

        <div class="form-group row">
            <label for="note-{{ $variable->id }}" class="col-sm-4 col-form-label">یادداشت</label>
            <div class="col-sm-8">
                <textarea id="note-{{ $variable->id }}" id="note" cols="30" rows="3" class="form-control rounded"></textarea>
                @error('note')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        @production
            <x-form.verification/>
        @endproduction

        <x-slot name="footer">
            <x-button wire:loading.attr="disabled" wire:click="update">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot>
    </x-modals.modal>
</div>
