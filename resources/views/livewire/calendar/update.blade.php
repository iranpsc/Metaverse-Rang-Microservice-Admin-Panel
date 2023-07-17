<div>
    <x-modals.modal id="edit-event-modal-{{ $event->id }}" title="ویرایش وقعه" size="modal-xl">
        <div @class([
            'd-none' => !$event->is_version,
        ])>
            <div class="input-group my-2">
                <input class="normal" wire:model="is_version" type="checkbox" id="is_version" @disabled($event->is_version)>
                <label for="is_version">این واقعه ورژن است</label>
            </div>
        </div>

        <div @class([
            'd-none' => !$event->is_version,
        ])>
            <x-forms.group for="version_title" label="شناسه نسخه">
                <x-forms.input id="version_title" wire:model="version_title" />
                @error('version_title')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>
        </div>

        <x-forms.group for="title-{{ $event->id }}" label="عنوان">
            <x-forms.input id="title-{{ $event->id }}" wire:model="title" />
            @error('title')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="content-{{ $event->id }}" label="متن">
            <textarea id="content-{{ $event->id }}">{{ $event->content }}</textarea>
            @error('content')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <div @class([
            'd-none' => $event->is_version,
        ])>
            <x-forms.group for="color-{{ $event->id }}" label="رنگ">
                <x-forms.input type="color" id="color-{{ $event->id }}" wire:model="color" />
                @error('color')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>
        </div>

        <div @class([
            'd-none' => $event->is_version,
        ])>
            <x-forms.group for="image-{{ $event->id }}" label="تصویر">
                <x-forms.input type="file" id="image-{{ $event->id }}" wire:model="image" />
                <x-progress-bar />
                @error('image')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>
        </div>
        <x-forms.group for="start_date-{{ $event->id }}" label="تاریخ شروع">
            <x-forms.input type="date" id="start_date-{{ $event->id }}" wire:model="start_date" />
            @error('start_date')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <div @class([
            'd-none' => $event->is_version,
        ])>
            <x-forms.group for="end_date-{{ $event->id }}" label="تاریخ پایان">
                <x-forms.input type="date" id="end_date-{{ $event->id }}" wire:model="end_date" />
                @error('end_date')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>
        </div>

        <div @class([
            'd-none' => $event->is_version,
        ])>
            <x-forms.group for="start_time-{{ $event->id }}" label="ساعت شروع">
                <x-forms.input class="clockpicker-now" type="time" id="start_time-{{ $event->id }}"
                    wire:model="start_time" />
                @error('start_time')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>
        </div>

        <div @class([
            'd-none' => $event->is_version,
        ])>
            <x-forms.group for="end_time-{{ $event->id }}" label="ساعت پایان">
                <x-forms.input class="clockpicker-now" type="time" id="end_time-{{ $event->id }}"
                    wire:model="end_time" />
                @error('end_time')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>
        </div>

        <x-forms.group for="btn_name" label="نام دکمه ورود به واقعه">
            <x-forms.input id="btn_name" wire:model="btn_name" />
            @error('btn_name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="btn_link" label="لینک دکمه ورود به واقعه">
            <x-forms.input id="btn_link" wire:model="btn_link" />
            @error('btn_link')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.verification />

        <x-slot name="footer">
            <x-buttons.btn-primary id="save-btn-{{ $event->id }}">ثبت
            </x-buttons.btn-primary>
            <x-buttons.btn-danger data-bs-dismiss="modal">بازگشت</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>

    <script>
        window.addEventListener('livewire:load', function() {
        var content{{ $event->id }} = CKEDITOR.replace('content-{{ $event->id }}');
        var saveBtn = document.getElementById('save-btn');

        CKEDITOR.editorConfig = function( config ) {
            config.language = 'fa';
            config.uiColor = '#F7B42C';
            config.height = 300;
            config.toolbarCanCollapse = true;
        };

        saveBtn.addEventListener('click', function() {
            @this.set('content', content{{ $event->id }}.getData());
            @this.call('save');
        });
        })
    </script>
</div>
