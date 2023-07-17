<div>
    <x-modals.modal size="modal-xl" title="ویرایش دسته بندی" id="edit-sub-category-modal-{{ $subCategory->id }}">
        <x-forms.group for="name" label="نام دسته بندی">
            <x-forms.input wire:model.lazy="name" />
            @error('name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="subcategory-description-{{ $subCategory->id }}" label="توضیحات">
            <div wire:ignore>
                <textarea id="subcategory-description-{{ $subCategory->id }}">{{ $subCategory->description }}</textarea>
            </div>
            @error('description')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="image" label="تصویر">
            <x-forms.input type="file" wire:model.lazy="image" />
            <x-progress-bar/>
            @error('image')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.verification/>

        <x-slot name="footer">
            <x-buttons.btn-success id="save-btn-{{ $subCategory->id }}">ثبت</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>

    <script>
        window.addEventListener('livewire:load', function() {
            var subCategoryDescription{{$subCategory->id}} = CKEDITOR.replace('subcategory-description-{{ $subCategory->id }}');
            var saveBtn = document.getElementById('save-btn-{{ $subCategory->id }}');

            CKEDITOR.editorConfig = function( config ) {
                config.language = 'fa';
                config.uiColor = '#F7B42C';
                config.height = 300;
                config.toolbarCanCollapse = true;
            };

            saveBtn.addEventListener('click', function() {
                @this.set('description', subCategoryDescription{{$subCategory->id}}.getData());
                @this.call('save');
            });
        })
    </script>
</div>
