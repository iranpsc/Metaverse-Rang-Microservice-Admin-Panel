<div>
    <x-modal id="edit-event-modal-{{ $event->id }}" title="ویرایش وقعه" size="modal-xl">

        <x-form.input name="title" label="عنوان" />

        <div class="form-group">
            <label for="content-{{ $event->id }}">متن</label>
            <div wire:ignore>
                <textarea id="content-{{ $event->id }}"></textarea>
            </div>
            @error('content')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <x-form.input type="color" name="color" label="رنگ" />

        <x-form.input type="file" name="image" label="تصویر" />

        <x-form.input type="date" name="start_date" label="تاریخ شروع" />

        <x-form.input type="date" name="end_date" label="تاریخ پایان" />

        <x-form.input type="time" name="start_time" label="ساعت شروع" />

        <x-form.input type="time" name="end_time" label="ساعت پایان" />

        <x-form.input name="btn_name" label="نام دکمه ورود به واقعه" />

        <x-form.input name="btn_link" label="لینک دکمه ورود به واقعه" />

        @production
            <x-form.verification />
        @endproduction

        <x-slot name="footer">
            <x-button id="save-btn-{{ $event->id }}">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بازگشت</x-button>
        </x-slot>
    </x-modals.modal>

</div>

@script
    <script>
        let content_{{ $event->id }} = CKEDITOR.replace('content-{{ $event->id }}');
        let saveBtn = document.getElementById('save-btn-{{ $event->id }}');

        content_{{ $event->id }}.setData(`{!! $event->content !!}`);

        CKEDITOR.editorConfig = function(config) {
            config.language = 'fa';
            config.uiColor = '#F7B42C';
            config.height = 300;
            config.toolbarCanCollapse = true;
        };

        saveBtn.addEventListener('click', function() {
            $wire.set('content', content_{{ $event->id }}.getData());
            $wire.call('save');
        });
    </script>
@endscript
