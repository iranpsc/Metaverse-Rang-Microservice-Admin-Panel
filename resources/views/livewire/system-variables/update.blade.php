<div>
    {{-- Success is as dangerous as failure. --}}
    <x-modals.modal id="edit-system-variable-{{ $variable->id }}" title="ویرایش متغیر">
        <x-forms.group for="name-{{ $variable->id }}" label="نام متغییر">
            <x-forms.input wire:model="name" id="name-{{ $variable->id }}" />
            @error('name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="slug-{{ $variable->id }}" label="اسلاگ">
            <x-forms.input type="slug" wire:model="slug" id="slug-{{ $variable->id }}" />
            @error('slug')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="value-{{ $variable->id }}" label="مقدار">
            <x-forms.input type="value" wire:model="value" id="value-{{ $variable->id }}" />
            @error('value')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="note-{{ $variable->id }}" label="یادداشت">
            <textarea id="note-{{ $variable->id }}" id="note" cols="30" rows="3" class="form-control rounded"
                wire:model="note"></textarea>
            @error('note')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.verification/>
        <x-slot name="footer">
            <x-buttons.btn-success wire:loading.attr="disabled" wire:click="update">ثبت</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>
</div>
