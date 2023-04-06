<div class="text-right">
    <div class="container my-2">
        @if (session()->has('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        @if (session()->has('error'))
            <x-alerts.danger>{{ session('error') }}</x-alerts.danger>
        @endif
    </div>

    <div class="row">
        <div class="col-md-6">
            <x-forms.group for="level-{{ $level->id }}-create-union" label="مجوز تاسیس اتحاد">
                <x-forms.select wire:model="create_union" id="level-{{ $level->id }}-create-union">
                    <option @selected($create_union) value="0">خیر</option>
                    <option @selected($create_union) value="1">بله</option>
                </x-forms.select>
                @error('create_union')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-add-member-to-union" label="مجوز عضوگیری برای اتحاد">
                <x-forms.select wire:model="add_memeber_to_union" id="level-{{ $level->id }}-add-member-to-union">
                    <option @selected($add_memeber_to_union) value="0">خیر</option>
                    <option @selected($add_memeber_to_union) value="1">بله</option>
                </x-forms.select>
                @error('add_memeber_to_union')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-observation-license" label="مجوز بازرسی">
                <x-forms.select wire:model="observation_license" id="level-{{ $level->id }}-observation-license">
                    <option @selected($observation_license) value="0">خیر</option>
                    <option @selected($observation_license) value="1">بله</option>
                </x-forms.select>
                @error('observation_license')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-gate-license" label="مجوز تاسیس دروازه">
                <x-forms.select wire:model="gate_license" id="level-{{ $level->id }}-gate-license">
                    <option @selected($gate_license) value="0">خیر</option>
                    <option @selected($gate_license) value="1">بله</option>
                </x-forms.select>
                @error('gate_license')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-lawyer-license" label="مجوز وکالت">
                <x-forms.select wire:model="lawyer_license" id="level-{{ $level->id }}-lawyer-license">
                    <option @selected($lawyer_license) value="0">خیر</option>
                    <option @selected($lawyer_license) value="1">بله</option>
                </x-forms.select>
                @error('lawyer_license')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-city-counsile-entry" label="مجوز ورود به شورای شهر">
                <x-forms.select wire:model="city_counsile_entry" id="level-{{ $level->id }}-city-counsile-entry">
                    <option @selected($city_counsile_entry) value="0">خیر</option>
                    <option @selected($city_counsile_entry) value="1">بله</option>
                </x-forms.select>
                @error('city_counsile_entry')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-establish-special-residential-property"
                label="مجوز تاسیس ملک مسکونی ویژه">
                <x-forms.select wire:model="establish_special_residential_property"
                    id="level-{{ $level->id }}-establish-special-residential-property">
                    <option @selected($establish_special_residential_property) value="0">خیر</option>
                    <option @selected($establish_special_residential_property) value="1">بله</option>
                </x-forms.select>
                @error('establish_special_residential_property')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-establish-property-on-surface"
                label="مجوز تاسیس ملک بر روی سطح">
                <x-forms.select wire:model="establish_property_on_surface"
                    id="level-{{ $level->id }}-establish-property-on-surface">
                    <option @selected($establish_property_on_surface) value="0">خیر</option>
                    <option @selected($establish_property_on_surface) value="1">بله</option>
                </x-forms.select>
                @error('establish_property_on_surface')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-judge-entry" label="ورود به قضاوت">
                <x-forms.select wire:model="judge_entry" id="level-{{ $level->id }}-judge-entry">
                    <option @selected($judge_entry) value="0">خیر</option>
                    <option @selected($judge_entry) value="1">بله</option>
                </x-forms.select>
                @error('judge_entry')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

        </div>
        <div class="col-md-6">

            <x-forms.group for="level-{{ $level->id }}-upload-image" label="بار گذاری تصاویر آزاد">
                <x-forms.select wire:model="upload_image" id="level-{{ $level->id }}-upload-image">
                    <option @selected($upload_image) value="0">خیر</option>
                    <option @selected($upload_image) value="1">بله</option>
                </x-forms.select>
                @error('upload_image')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-delete-image" label="حذف تصاویر آزاد">
                <x-forms.select wire:model="delete_image" id="level-{{ $level->id }}-delete-image">
                    <option @selected($delete_image) value="0">خیر</option>
                    <option @selected($delete_image) value="1">بله</option>
                </x-forms.select>
                @error('delete_image')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-inter-level-general-points"
                label=" ثبت موقعیت های عمومی سطح">
                <x-forms.select wire:model="inter_level_general_points"
                    id="level-{{ $level->id }}-inter-level-general-points">
                    <option @selected($inter_level_general_points) value="0">خیر</option>
                    <option @selected($inter_level_general_points) value="1">بله</option>
                </x-forms.select>
                @error('inter_level_general_points')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-inter-level-special-points"
                label=" ثبت موقعیت های ویژه سطح">
                <x-forms.select wire:model="inter_level_special_points"
                    id="level-{{ $level->id }}-inter-level-special-points">
                    <option @selected($inter_level_special_points) value="0">خیر</option>
                    <option @selected($inter_level_special_points) value="1">بله</option>
                </x-forms.select>
                @error('inter_level_special_points')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-rent-out-satisfaction"
                label=" کرایه با واحد رضایت">
                <x-forms.select wire:model="rent_out_satisfaction"
                    id="level-{{ $level->id }}-rent-out-satisfaction">
                    <option @selected($rent_out_satisfaction) value="0">خیر</option>
                    <option @selected($rent_out_satisfaction) value="1">بله</option>
                </x-forms.select>
                @error('rent_out_satisfaction')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-access-to-answer-questions-unit"
                label="دسترسی به بخش پاسخ دهی به سوالات">
                <x-forms.select wire:model="access_to_answer_questions_unit"
                    id="level-{{ $level->id }}-access-to-answer-questions-unit">
                    <option @selected($access_to_answer_questions_unit) value="0">خیر</option>
                    <option @selected($access_to_answer_questions_unit) value="1">بله</option>
                </x-forms.select>
                @error('access_to_answer_questions_unit')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-create-challenge-questions"
                label=" طرح سوال در چالش سوالات">
                <x-forms.select wire:model="create_challenge_questions"
                    id="level-{{ $level->id }}-create-challenge-questions">
                    <option @selected($create_challenge_questions) value="0">خیر</option>
                    <option @selected($create_challenge_questions) value="1">بله</option>
                </x-forms.select>
                @error('create_challenge_questions')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-upload-music"
                label=" بارگذاری موزیک در لیست انتظار">
                <x-forms.select wire:model="upload_music"
                    id="level-{{ $level->id }}-upload-music">
                    <option @selected($upload_music) value="0">خیر</option>
                    <option @selected($upload_music) value="1">بله</option>
                </x-forms.select>
                @error('upload_music')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>
        </div>
    </div>
    <hr>
    <x-buttons.btn-primary class="w-25" wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-primary>
</div>
