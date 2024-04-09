<div>
    <x-modal id="edit-message-{{ $message->id }}" title="ویرایش پیام">
        <div class="form-group">
            <label>متن پیام</label>
            <div wire:ignore>
                <textarea id="content-{{ $message->id }}"></textarea>
            </div>
            @error('content')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </div>
        <x-slot name="footer">
            <x-button id="save-btn-{{ $message->id }}">ذخیره</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot>
    </x-modals.modal>

</div>

@script
    <script>
        let content_{{ $message->id }} = CKEDITOR.replace('content-{{ $message->id }}');
        let saveBtn = document.getElementById('save-btn-{{ $message->id }}');

        content_{{ $message->id }}.setData(`{{ $message->message }}`);

        CKEDITOR.editorConfig = function(config) {
            config.language = 'fa';
            config.uiColor = '#F7B42C';
            config.height = 300;
            config.toolbarCanCollapse = true;
        };

        saveBtn.addEventListener('click', function() {
            @this.set('content', content_{{ $message->id }}.getData());
            @this.call('save');
        });
    </script>
@endscript
