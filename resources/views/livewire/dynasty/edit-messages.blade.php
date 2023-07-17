<div>
    <x-modals.modal id="edit-message-{{ $message->id }}" title="ویرایش پیام">
        <div class="form-group">
            <label>متن پیام</label>
            <div wire:ignore>
                <textarea id="content-{{ $message->id }}">{{ $message->message }}</textarea>
            </div>
            @error('content')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </div>
        <x-slot name="footer">
            <x-buttons.btn-primary id="save-btn-{{ $message->id }}">ذخیره</x-buttons.btn-primary>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>

    <script>
        window.addEventListener('livewire:load', function() {
            var content{{$message->id}} = CKEDITOR.replace('content-{{$message->id}}');
            var saveBtn = document.getElementById('save-btn-{{$message->id}}');

            CKEDITOR.editorConfig = function( config ) {
                config.language = 'fa';
                config.uiColor = '#F7B42C';
                config.height = 300;
                config.toolbarCanCollapse = true;
            };

            saveBtn.addEventListener('click', function() {
                @this.set('content', content{{$message->id}}.getData());
                @this.call('save');
            });
        })
    </script>
</div>
