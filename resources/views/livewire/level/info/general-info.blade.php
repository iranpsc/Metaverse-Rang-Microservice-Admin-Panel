<div>
    <div class="row">
        <div class="col-md-6">

            <x-form.input name="score" label="امتیاز مورد نیاز" />

            <x-form.input name="rank" label="رتبه سطح" />

            <x-form.input name="subcategories" label="تعداد زیر شاخه" />

            <x-form.input name="creation_date" label="تاریخ ایجاد" />

            <x-form.input name="english_font" label="فونت مورد استفاده انگلیسی" />

            <x-form.input name="persian_font" label="فونت مورد استفاده فارسی" />

            <div class="form-group row">
                <label for="level-{{ $level->id }}-general-info-animation"
                    class="col-md-4 col-form-label">انیمیشن</label>
                <div class="col-md-8">
                    <select wire:model="has_animation" id="level-{{ $level->id }}-general-info-animation"
                        class="form-control rounded">
                        <option @selected($has_animation) value="0">ندارد</option>
                        <option @selected($has_animation) value="1">دارد</option>
                    </select>
                    @error('animation')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <x-form.input type="file" name="png_file" label="فایل png" />

        </div>
        <div class="col-md-6">

            <x-form.input type="file" name="gif_file" label="فایل gif" />

            <x-form.input type="file" name="fbx_file" label="فایل fbx" />

            <x-form.input name="file_volume" label="حجم فایل" />

            <x-form.input name="used_colors" label="رنگ های مورد استفاده" />

            <x-form.input name="points" label="تعداد پوینت های سطح" />

            <x-form.input name="lines" label="تعداد خطوط مدل سطح" />

            <x-form.input name="designer" label="طراح سطح" />

            <x-form.input name="model_designer" label="طراح مدل سه بعدی" />

        </div>
    </div>

    <div class="form-group my-2">
        <label for="level-{{ $level->id }}-description">توضیحات سطح</label>
        <div wire:ignore>
            <textarea id="level-{{ $level->id }}-description"></textarea>
        </div>
        @error('description')
            <span class="form-text text-danger">{{ $message }}</span>
        @enderror
    </div>

    <hr>
    <x-form.verification />
    <hr>

    <x-button class="w-25" id="save-btn-{{ $level->id }}-general-info">ثبت</x-button>
</div>

@script
    <script>
        let level_{{ $level->id }}_general_info_description = CKEDITOR.replace(
            'level-{{ $level->id }}-description');

        let saveBtn_{{ $level->id }} = document.getElementById('save-btn-{{ $level->id }}-general-info');

        level_{{ $level->id }}_general_info_description.setData(`{{ $description }}`);

        CKEDITOR.editorConfig = function(config) {
            config.language = 'fa';
            config.uiColor = '#F7B42C';
            config.height = 300;
            config.toolbarCanCollapse = true;
        };

        saveBtn_{{ $level->id }}.addEventListener('click', function() {
            @this.set('description', level_{{ $level->id }}_general_info_description.getData());
            @this.call('save');
        });
    </script>
@endscript
