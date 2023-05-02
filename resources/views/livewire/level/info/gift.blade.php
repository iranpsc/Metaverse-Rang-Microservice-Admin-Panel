<div class="text-right">
    <div class="row">
        <div class="col-md-6">

            <x-forms.group for="level-{{ $level->id }}-gift-name" label="نام هدیه همراه">
                <x-forms.input id="level-{{ $level->id }}-gift-name" wire:model="name" />
                @error('name')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-mothly-capcity-count" label="تعداد ظرفیت ماهانه">
                <x-forms.input id="level-{{ $level->id }}-mothly-capcity-count" wire:model="monthly_capacity_count" />
                @error('monthly_capacity_count')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-store-capacity" label="قابلیت ذخیره ظرفیت">
                <x-forms.select wire:model="store_capacity" id="level-{{ $level->id }}-store-capacity">
                    <option @selected($store_capacity) value="0">خیر</option>
                    <option @selected($store_capacity) value="1">بله</option>
                </x-forms.select>
                @error('store_capacity')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-sell-capacity" label="قابلیت فروش ظرفیت">
                <x-forms.select wire:model="sell_capacity" id="level-{{ $level->id }}-sell-capacity">
                    <option @selected($sell_capacity) value="0">خیر</option>
                    <option @selected($sell_capacity) value="1">بله</option>
                </x-forms.select>
                @error('sell_capacity')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-three_dmodel-volume" label="حجم مدل سه بعدی هدیه همراه">
                <x-forms.input id="level-{{ $level->id }}-three_dmodel-volume" wire:model="three_d_model_volume" />
                @error('three_d_model_volume')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-three_dmodel-points" label="تعداد پوینت های مدل سه بعدی هدیه همراه">
                <x-forms.input id="level-{{ $level->id }}-three_dmodel-points" wire:model="three_d_model_points" />
                @error('three_d_model_points')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-three_dmodel-lines" label="تعداد خطوط مدل سه بعدی هدیه همراه">
                <x-forms.input id="level-{{ $level->id }}-three_dmodel-lines" wire:model="three_d_model_lines" />
                @error('three_d_model_lines')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-has-animation" label="انیمیشن">
                <x-forms.select wire:model="has_animation" id="level-{{ $level->id }}-has-animation">
                    <option @selected($has_animation) value="0">ندارد</option>
                    <option @selected($has_animation) value="1">دارد</option>
                </x-forms.select>
                @error('has_animation')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

        </div>
        <div class="col-md-6">

            <x-forms.group for="level-{{ $level->id }}-vod-document-registration" label="ثبت سند VOD برای هدیه همراه">
                <x-forms.select wire:model="vod_document_registration" id="level-{{ $level->id }}-vod-document-registration">
                    <option @selected($vod_document_registration) value="0">خیر</option>
                    <option @selected($vod_document_registration) value="1">بله</option>
                </x-forms.select>
                @error('vod_document_registration')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-seller-link" label="لینک دسترسی به فروشندگان">
                <x-forms.input id="level-{{ $level->id }}-seller-link" wire:model="seller_link" />
                @error('seller_link')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-gift-png-file" label="فایل png هدیه همراه">
                <x-forms.input type="file" id="level-{{ $level->id }}-gift-png-file" wire:model="png_file" />
                <x-progress-bar/>
                @error('png_file')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-gift-fbx-file" label="فایل fbx هدیه همراه">
                <x-forms.input type="file" id="level-{{ $level->id }}-gift-fbx-file" wire:model="fbx_file" />
                <x-progress-bar/>
                @error('fbx_file')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-gift-gif-file" label="فایل gif هدیه همراه">
                <x-forms.input type="file" id="level-{{ $level->id }}-gift-gif-file" wire:model="gif_file" />
                <x-progress-bar/>
                @error('gif_file')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-sell-gift" label="قابلیت فروش هدیه همراه">
                <x-forms.select wire:model="sell" id="level-{{ $level->id }}-sell-gift">
                    <option @selected($sell) value="0">خیر</option>
                    <option @selected($sell) value="1">بله</option>
                </x-forms.select>
                @error('sell')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-rent-gift" label="قابلیت کرایه هدیه همراه">
                <x-forms.select wire:model="rent" id="level-{{ $level->id }}-rent-gift">
                    <option @selected($rent) value="0">خیر</option>
                    <option @selected($rent) value="1">بله</option>
                </x-forms.select>
                @error('rent')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-gift-designer" label="طراح هدیه">
                <x-forms.input id="level-{{ $level->id }}-gift-designer" wire:model="designer" />
                @error('designer')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

        </div>
    </div>

    <div class="form-group my-2">
        <label for="level-{{ $level->id }}-gift-description">توضیحات هدیه همراه</label>
        <textarea class="form-control rounded" wire:model="description" id="level-{{ $level->id }}-gift-description" cols="30" rows="5"></textarea>
        @error('description')
            <span class="form-text text-danger">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group my-2">
        <label for="level-{{ $level->id }}-gift-features">قابلیت های هدیه همراه</label>
        <textarea class="form-control rounded" wire:model="features" id="level-{{ $level->id }}-gift-features" cols="30" rows="5"></textarea>
        @error('features')
            <span class="form-text text-danger">{{ $message }}</span>
        @enderror
    </div>

    <hr>
    <x-forms.verification/>
    <hr>

    <x-buttons.btn-primary class="w-25" wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-primary>

</div>
