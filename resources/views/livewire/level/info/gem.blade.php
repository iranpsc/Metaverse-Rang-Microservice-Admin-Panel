<div>

    <div class="row">
        <div class="col-md-6">

            <x-form.input name="name" label="نام سنگ" />


            <x-form.input name="thread" label="تراشه نگین" />

            <x-form.input name="points" label="تعداد پوینت های نگین" />

            <x-form.input name="volume" label="حجم نگین" />

            <x-form.input name="color" label="رنگ نگین" />


            <div class="form-group row">
                <label for="level-{{ $level->id }}-gem-animation" class="col-md-4 col-form-label">انیمیشن</label>
                <div class="col-md-8">
                    <select wire:model="has_animation" id="level-{{ $level->id }}-gem-animation"
                        class="form-control rounded">
                        <option @selected($has_animation) value="0">ندارد</option>
                        <option @selected($has_animation) value="1">دارد</option>
                    </select>
                    @error('animation')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

        </div>
        <div class="col-md-6">

            <x-form.input type="file" name="png_file" label="فایل png نگین" />

            <x-form.input type="file" name="fbx_file" label="فایل fbx نگین" />

            <x-form.input name="lines" label="تعداد خطوط مدل سه بعدی سنگ" />

            <div class="form-group row">
                <label for="level-{{ $level->id }}-encryption" class="col-md-4 col-form-label">رمزگذاری</label>
                <div class="col-md-8">
                    <select class="form-control rounded" wire:model="encryption"
                        id="level-{{ $level->id }}-encryption">
                        <option @selected($encryption) value="0">خیر</option>
                        <option @selected($encryption) value="1">بله</option>
                    </select>
                    @error('encryption')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <x-form.input name="designer" label="طراح نگین" />

        </div>
    </div>

    <div class="form-group my-2">
        <label for="level-{{ $level->id }}-gem-description">توضیحات نگین</label>
        <div wire:ignore>
            <textarea id="level-{{ $level->id }}-gem-description">{{ $description }}</textarea>
        </div>
        @error('description')
            <span class="form-text text-danger">{{ $message }}</span>
        @enderror
    </div>

    <hr>
    <x-form.verification />
    <hr>

    <x-button class="w-25" id="save-btn-{{ $level->id }}-gem">ثبت</x-button>

</div>

@script
    <script>
        let level_{{ $level->id }}_gem_description = CKEDITOR.replace(
            'level-{{ $level->id }}-gem-description');
        let saveBtn{{ $level->id }} = document.getElementById('save-btn-{{ $level->id }}-gem');

        CKEDITOR.editorConfig = function(config) {
            config.language = 'fa';
            config.uiColor = '#F7B42C';
            config.height = 300;
            config.toolbarCanCollapse = true;
        };

        saveBtn{{ $level->id }}.addEventListener('click', function() {
            @this.set('description', level_{{ $level->id }}_gem_description.getData());
            @this.call('save');
        });
    </script>
@endscript
