<div>
    <x-modals.modal size="modal-xl" title="ویرایش دسته بندی" id="edit-category-modal-{{ $category->id }}">
        <x-forms.group for="name-{{ $category->id }}" label="نام دسته بندی">
            <x-forms.input wire:model.lazy="name" />
            @error('name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="description-{{ $category->id }}" label="توضیحات">
            <div wire:ignore>
                <textarea id="description-{{ $category->id }}">{{ $category->description }}</textarea>
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

        <x-forms.group for="icon" label="آیکون">
            <x-forms.input type="file" wire:model.lazy="icon" />
            <x-progress-bar />
            @error('icon')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.verification/>

        <x-slot name="footer">
            <x-buttons.btn-success id="save-btn-{{ $category->id }}">ثبت</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>

    <script>
        window.addEventListener('livewire:load', function() {
            var description{{$category->id}} = CKEDITOR.replace('description-{{ $category->id }}');
            var saveBtn = document.getElementById('save-btn-{{ $category->id }}');

            CKEDITOR.editorConfig = function( config ) {
                config.language = 'fa';
                config.uiColor = '#F7B42C';
                config.height = 300;
                config.toolbarCanCollapse = true;
            };

            saveBtn.addEventListener('click', function() {
                @this.set('description', description{{$category->id}}.getData());
                @this.call('save');
            });
        })
    </script>
</div>
