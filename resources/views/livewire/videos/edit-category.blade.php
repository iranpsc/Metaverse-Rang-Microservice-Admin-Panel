<div>
    <x-modal size="modal-xl" title="ویرایش دسته بندی" id="edit-category-modal-{{ $category->id }}">
        <x-form.input name="name" label="نام" />

        <div class="form-group">
            <label for="description-{{ $category->id }}">توضیحات</label>
            <div wire:ignore>
                <textarea class="form-control" id="description-{{ $category->id }}">{{ $category->description }}</textarea>
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
            <x-button id="save-btn-{{ $category->id }}">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>

</div>

@script
    <script>
        let description{{ $category->id }} = CKEDITOR.replace('description-{{ $category->id }}');
        let saveBtn = document.getElementById('save-btn-{{ $category->id }}');

        CKEDITOR.editorConfig = function(config) {
            config.language = 'fa';
            config.uiColor = '#F7B42C';
            config.height = 300;
            config.toolbarCanCollapse = true;
        };

        saveBtn.addEventListener('click', function() {
            $wire.set('description', description{{ $category->id }}.getData());
            $wire.call('save');
        });
    </script>
@endscript
