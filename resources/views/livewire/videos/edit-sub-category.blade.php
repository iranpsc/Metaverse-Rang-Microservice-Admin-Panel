<div>
    <x-modals.modal title="ویرایش دسته بندی" id="edit-sub-category-modal-{{ $subCategory->id }}">
        @if (session('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        <x-forms.group for="name" label="نام دسته بندی">
            <x-forms.input wire:model.lazy="name" />
            @error('name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="description" label="توضیحات">
            <textarea wire:model.lazy="description" class="form-control rounded" id="description" cols="30" rows="10"></textarea>
            @error('description')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="image" label="تصویر">
            <x-forms.input type="file" wire:model.lazy="image" />
            <span class="text-success" wire:loading wire:target="image">در حال بارگذاری ...</span>
            @error('image')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.verification id="{{ $subCategory->id }}"/>

        <x-slot name="footer">
            <x-buttons.btn-success wire:click="save" wire:loading.attr="disabled">ثبت</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>
</div>
