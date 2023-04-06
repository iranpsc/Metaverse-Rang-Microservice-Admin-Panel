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
        <textarea class="form-control rounded" wire:model="description" id="level-{{ $level->id }}-gift-description" cols="30" rows="10"></textarea>
        @error('description')
            <span class="form-text text-danger">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group my-2">
        <label for="level-{{ $level->id }}-gift-features">قابلیت های هدیه همراه</label>
        <textarea class="form-control rounded" wire:model="features" id="level-{{ $level->id }}-gift-features" cols="30" rows="10"></textarea>
        @error('features')
            <span class="form-text text-danger">{{ $message }}</span>
        @enderror
    </div>

    <hr>

    <x-buttons.btn-primary class="w-25" wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-primary>

</div>
