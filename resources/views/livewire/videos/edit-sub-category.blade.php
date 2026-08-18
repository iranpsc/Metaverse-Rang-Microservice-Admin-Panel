<div>
    <x-modal size="modal-xl" title="ویرایش دسته بندی" id="edit-sub-category-modal-{{ $subCategory->id }}">
        <x-form.input name="name" label="نام" />


        <div class="form-group">
            <label for="subcategory-description-{{ $subCategory->id }}">توضیحات</label>
            <div wire:ignore>
                <textarea class="form-control" id="subcategory-description-{{ $subCategory->id }}">{{ $subCategory->description }}</textarea>
            </div>
            @error('description')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <x-form.input type="file" name="image" label="تصویر" />

        <x-form.input type="file" name="icon" label="آیکون" />

        @production
            <x-form.verification />
        @endproduction

        <x-slot name="footer">
            <x-button id="save-btn-{{ $subCategory->id }}">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot>
    </x-modals.modal>
</div>

@script
    <script>
        let subCategoryDescription{{ $subCategory->id }} = CKEDITOR.replace(
            'subcategory-description-{{ $subCategory->id }}');
        let saveBtn = document.getElementById('save-btn-{{ $subCategory->id }}');

        CKEDITOR.editorConfig = function(config) {
            config.language = 'fa';
            config.uiColor = '#F7B42C';
            config.height = 300;
            config.toolbarCanCollapse = true;
        };

        saveBtn.addEventListener('click', function() {
            $wire.set('description', subCategoryDescription{{ $subCategory->id }}.getData());
            $wire.call('save');
        });
    </script>
@endscript
