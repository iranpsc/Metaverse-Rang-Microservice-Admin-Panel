<div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group row">
                <label for="level-{{ $level->id }}-create-union" class="col-md-4 col-form-label">مجوز تاسیس اتحاد</label>
                <select class="form-control rounded" wire:model="create_union">
                    <option @selected($create_union) value="0">خیر</option>
                    <option @selected($create_union) value="1">بله</option>
                </select>
                @error('create_union')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group row">
                <label for="level-{{ $level->id }}-add-member-to-union" class="col-md-4 col-form-label">مجوز عضوگیری
                    برای اتحاد</label>
                <select class="form-control rounded" wire:model="add_memeber_to_union">
                    <option @selected($add_memeber_to_union) value="0">خیر</option>
                    <option @selected($add_memeber_to_union) value="1">بله</option>
                </select>
                @error('add_memeber_to_union')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group row">
                <label for="level-{{ $level->id }}-observation-license" class="col-md-4 col-form-label">مجوز بازرسی</label>
                <select class="form-control rounded" wire:model="observation_license">
                    <option @selected($observation_license) value="0">خیر</option>
                    <option @selected($observation_license) value="1">بله</option>
                </select>
                @error('observation_license')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group row">
                <label for="level-{{ $level->id }}-gate-license" class="col-md-4 col-form-label">مجوز تاسیس دروازه</label>
                <select class="form-control rounded" wire:model="gate_license">
                    <option @selected($gate_license) value="0">خیر</option>
                    <option @selected($gate_license) value="1">بله</option>
                </select>
                @error('gate_license')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group row">
                <label for="level-{{ $level->id }}-lawyer-license" class="col-md-4 col-form-label">مجوز وکالت</label>
                <select class="form-control rounded" wire:model="lawyer_license">
                    <option @selected($lawyer_license) value="0">خیر</option>
                    <option @selected($lawyer_license) value="1">بله</option>
                </select>
                @error('lawyer_license')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group row">
                <label for="level-{{ $level->id }}-city-counsile-entry" class="col-md-4 col-form-label">مجوز ورود به
                    شورای شهر</label>
                <select class="form-control rounded" wire:model="city_counsile_entry">
                    <option @selected($city_counsile_entry) value="0">خیر</option>
                    <option @selected($city_counsile_entry) value="1">بله</option>
                </select>
                @error('city_counsile_entry')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group row">
                <label for="level-{{ $level->id }}-establish-special-residential-property"
                    class="col-md-4 col-form-label">مجوز تاسیس ملک مسکونی ویژه</label>
                <select class="form-control rounded" wire:model="establish_special_residential_property">
                    <option @selected($establish_special_residential_property) value="0">خیر</option>
                    <option @selected($establish_special_residential_property) value="1">بله</option>
                </select>
                @error('establish_special_residential_property')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group row">
                <label for="level-{{ $level->id }}-establish-property-on-surface"
                    class="col-md-4 col-form-label">مجوز تاسیس ملک بر روی سطح</label>
                <select class="form-control rounded" wire:model="establish_property_on_surface">
                    <option @selected($establish_property_on_surface) value="0">خیر</option>
                    <option @selected($establish_property_on_surface) value="1">بله</option>
                </select>
                @error('establish_property_on_surface')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group row">
                <label for="level-{{ $level->id }}-establish-property-on-surface"
                    class="col-md-4 col-form-label">مجوز تاسیس ملک بر روی سطح</label>
                <select class="form-control rounded" wire:model="establish_property_on_surface">
                    <option @selected($establish_property_on_surface) value="0">خیر</option>
                    <option @selected($establish_property_on_surface) value="1">بله</option>
                </select>
                @error('establish_property_on_surface')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>

        </div>
        <div class="col-md-6">

            <div class="form-group row">
                <label for="level-{{ $level->id }}-upload-image" class="col-md-4 col-form-label">بار گذاری تصاویر آزاد</label>
                <div class="col-md-8">
                    <select wire:model="upload_image" id="level-{{ $level->id }}-upload-image"
                        class="form-control rounded">
                        <option @selected($upload_image) value="0">خیر</option>
                        <option @selected($upload_image) value="1">بله</option>
                    </select>
                    @error('upload_image')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="level-{{ $level->id }}-delete-image" class="col-md-4 col-form-label">حذف تصاویر آزاد</label>
                <div class="col-md-8">
                    <select wire:model="delete_image" id="level-{{ $level->id }}-delete-image"
                        class="form-control rounded">
                        <option @selected($delete_image) value="0">خیر</option>
                        <option @selected($delete_image) value="1">بله</option>
                    </select>
                    @error('delete_image')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="level-{{ $level->id }}-inter-level-general-points"
                    class="col-md-4 col-form-label">ثبت موقعیت های عمومی سطح</label>
                <div class="col-md-8">
                    <select wire:model="inter_level_general_points" id="level-{{ $level->id }}-inter-level-general-points"
                        class="form-control rounded">
                        <option @selected($inter_level_general_points) value="0">خیر</option>
                        <option @selected($inter_level_general_points) value="1">بله</option>
                    </select>
                    @error('inter_level_general_points')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="level-{{ $level->id }}-inter-level-special-points"
                    class="col-md-4 col-form-label">ثبت موقعیت های ویژه سطح</label>
                <div class="col-md-8">
                    <select wire:model="inter_level_special_points" id="level-{{ $level->id }}-inter-level-special-points"
                        class="form-control rounded">
                        <option @selected($inter_level_special_points) value="0">خیر</option>
                        <option @selected($inter_level_special_points) value="1">بله</option>
                    </select>
                    @error('inter_level_special_points')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>


            <div class="form-group row">
                <label for="level-{{ $level->id }}-rent-out-satisfaction" class="col-md-4 col-form-label">کرایه با واحد
                    رضایت</label>
                <div class="col-md-8">
                    <select wire:model="rent_out_satisfaction" id="level-{{ $level->id }}-rent-out-satisfaction"
                        class="form-control rounded">
                        <option @selected($rent_out_satisfaction) value="0">خیر</option>
                        <option @selected($rent_out_satisfaction) value="1">بله</option>
                    </select>
                    @error('rent_out_satisfaction')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="level-{{ $level->id }}-access-to-answer-questions-unit"
                    class="col-md-4 col-form-label">دسترسی به بخش پاسخ دهی به سوالات</label>
                <div class="col-md-8">
                    <select wire:model="access_to_answer_questions_unit"
                        id="level-{{ $level->id }}-access-to-answer-questions-unit" class="form-control rounded">
                        <option @selected($access_to_answer_questions_unit) value="0">خیر</option>
                        <option @selected($access_to_answer_questions_unit) value="1">بله</option>
                    </select>
                    @error('access_to_answer_questions_unit')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="level-{{ $level->id }}-create-challenge-questions"
                    class="col-md-4 col-form-label">طرح سوال در چالش سوالات</label>
                <div class="col-md-8">
                    <select wire:model="create_challenge_questions" id="level-{{ $level->id }}-create-challenge-questions"
                        class="form-control rounded">
                        <option @selected($create_challenge_questions) value="0">خیر</option>
                        <option @selected($create_challenge_questions) value="1">بله</option>
                    </select>
                    @error('create_challenge_questions')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="level-{{ $level->id }}-upload-music" class="col-md-4 col-form-label">بارگذاری موزیک در لیست
                    انتظار</label>
                <div class="col-md-8">
                    <select wire:model="upload_music" id="level-{{ $level->id }}-upload-music" class="form-control rounded">
                        <option @selected($upload_music) value="0">خیر</option>
                        <option @selected($upload_music) value="1">بله</option>
                    </select>
                    @error('upload_music')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <x-form.verification />

    <x-button class="w-25" wire:click="save">ثبت</x-button>
</div>
