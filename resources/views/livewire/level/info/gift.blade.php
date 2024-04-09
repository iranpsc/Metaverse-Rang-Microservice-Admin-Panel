<div>
    <div class="row">
        <div class="col-md-6">

            <x-form.input name="name" label="نام هدیه همراه" />

            <x-form.input name="monthly_capacity_count" label="تعداد ظرفیت ماهانه" />

            <div class="form-group row">
                <label for="level-{{ $level->id }}-sell-capacity" class="col-md-4 col-form-label">قابلیت فروش ظرفیت</label>
                <div class="col-md-8">
                    <select wire:model="sell_capacity" id="level-{{ $level->id }}-sell-capacity"
                        class="form-control">
                        <option @selected($sell_capacity) value="0">خیر</option>
                        <option @selected($sell_capacity) value="1">بله</option>
                    </select>
                    @error('sell_capacity')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <x-form.input name="three_d_model_volume" label="حجم مدل سه بعدی هدیه همراه" />

            <x-form.input name="three_d_model_points" label="تعداد پوینت های مدل سه بعدی هدیه همراه" />

            <x-form.input name="three_d_model_lines" label="تعداد خطوط مدل سه بعدی هدیه همراه" />

            <div class="form-group row">
                <label for="level-{{ $level->id }}-has-animation" class="col-md-4 col-form-label">انیمیشن</label>
                <div class="col-md-8">
                    <select wire:model="has_animation" id="level-{{ $level->id }}-has-animation"
                        class="form-control rounded">
                        <option @selected($has_animation) value="0">ندارد</option>
                        <option @selected($has_animation) value="1">دارد</option>
                    </select>
                    @error('has_animation')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <x-form.input name="vod_count" label="تعداد vod هدیه همراه" />

            <x-form.input name="start_vod_id" label="شناسه شروع vod هدیه همراه" />

            <x-form.input name="end_vod_id" label="شناسه پایان vod هدیه همراه" />
        </div>
        <div class="col-md-6">

            <div class="form-group row">
                <label for="level-{{ $level->id }}-vod-document-registration" class="col-md-4 col-form-label">ثبت سند VOD برای هدیه همراه</label>
                <div class="col-md-8">
                    <select wire:model="vod_document_registration"
                        id="level-{{ $level->id }}-vod-document-registration" class="form-control rounded">
                        <option @selected($vod_document_registration) value="0">خیر</option>
                        <option @selected($vod_document_registration) value="1">بله</option>
                    </select>
                    @error('vod_document_registration')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <x-form.input name="seller_link" label="لینک دسترسی به فروشندگان" />

            <x-form.input type="file" name="png_file" label="فایل png هدیه همراه" />

            <x-form.input type="file" name="fbx_file" label="فایل fbx هدیه همراه" />

            <x-form.input type="file" name="gif_file" label="فایل gif هدیه همراه" />

            <div class="form-group row">
                <label for="level-{{ $level->id }}-sell-gift" class="col-md-4 col-form-label">قابلیت فروش هدیه همراه</label>
                <div class="col-md-8">
                    <select wire:model="sell" id="level-{{ $level->id }}-sell-gift" class="form-control rounded">
                        <option @selected($sell) value="0">خیر</option>
                        <option @selected($sell) value="1">بله</option>
                    </select>
                    @error('sell')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="level-{{ $level->id }}-rent-gift" class="col-md-4 col-form-label">قابلیت کرایه هدیه همراه</label>
                <div class="col-md-8">
                    <select wire:model="rent" id="level-{{ $level->id }}-rent-gift" class="form-control rounded">
                        <option @selected($rent) value="0">خیر</option>
                        <option @selected($rent) value="1">بله</option>
                    </select>
                    @error('rent')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <x-form.input name="designer" label="طراح هدیه" />
        </div>
    </div>

    <div class="form-group my-2">
        <label for="level-{{ $level->id }}-gift-description">توضیحات هدیه همراه</label>
        <div wire:ignore>
            <textarea id="level-{{ $level->id }}-gift-description">{{ $description }}</textarea>
        </div>
        @error('description')
            <span class="form-text text-danger">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group my-2">
        <label for="level-{{ $level->id }}-gift-features">قابلیت های هدیه همراه</label>
        <div wire:ignore>
            <textarea id="level-{{ $level->id }}-gift-features">{{ $features }}</textarea>
        </div>
        @error('features')
            <span class="form-text text-danger">{{ $message }}</span>
        @enderror
    </div>

    <hr>
    <x-form.verification />
    <hr>

    <x-button class="w-25" id="save-btn-{{ $level->id }}-gifts">ثبت</x-button>

</div>

@script
    <script>
        let level_{{ $level->id }}_gift_description = CKEDITOR.replace('level-{{ $level->id }}-gift-description');
        let level_{{ $level->id }}_gift_features = CKEDITOR.replace('level-{{ $level->id }}-gift-features');
        let saveBtn{{ $level->id }} = document.getElementById('save-btn-{{ $level->id }}-gifts');

        CKEDITOR.editorConfig = function(config) {
            config.language = 'fa';
            config.uiColor = '#F7B42C';
            config.height = 300;
            config.toolbarCanCollapse = true;
        };

        saveBtn{{ $level->id }}.addEventListener('click', function() {
            @this.set('description', level_{{ $level->id }}_gift_description.getData());
            @this.set('features', level_{{ $level->id }}_gift_features.getData());
            @this.call('save');
        });
    </script>
@endscript
