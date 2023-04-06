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

            <x-forms.group for="level-{{ $level->id }}-score" label="امتیاز مورد نیاز">
                <x-forms.input id="level-{{ $level->id }}-score" wire:model="score" />
                @error('score')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-rank" label="رتبه سطح">
                <x-forms.input id="level-{{ $level->id }}-rank" wire:model="rank" />
                @error('rank')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-subcategories" label="تعداد زیرشاخه">
                <x-forms.input id="level-{{ $level->id }}-subcategories" wire:model="subcategories" />
                @error('subcategories')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-created-at" label="تاریخ ایجاد سطح">
                <x-forms.input id="level-{{ $level->id }}-created-at" wire:model="creation_date" />
                @error('creation_date')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-persian-font" label="فونت مورد استفاده فارسی">
                <x-forms.input id="level-{{ $level->id }}-persian-font" wire:model="persian_font" />
                @error('persian_font')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>
            <x-forms.group for="level-{{ $level->id }}-english-font" label="فونت مورد استفاده انگلیسی">
                <x-forms.input id="level-{{ $level->id }}-english-font" wire:model="english_font" />
                @error('english_font')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

        </div>
        <div class="col-md-6">

            <x-forms.group for="level-{{ $level->id }}-file-volume" label="حجم فایل">
                <x-forms.input id="level-{{ $level->id }}-file-volume" wire:model="file_volume" />
                @error('file_volume')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-used-colors" label="رنگ های مورد استفاده">
                <x-forms.input id="level-{{ $level->id }}-used-colors" wire:model="used_colors" />
                @error('used_colors')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-points" label="تعداد پوینت های سطح">
                <x-forms.input id="level-{{ $level->id }}" wire:model="points" />
                @error('points')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-designer" label="طراح سطح">
                <x-forms.input id="level-{{ $level->id }}" wire:model="designer" />
                @error('designer')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-3d-model-designer" label="طراح مدل سه بعدی">
                <x-forms.input id="level-{{ $level->id }}-3d-model-designer" wire:model="model_designer" />
                @error('model_designer')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

        </div>
    </div>

    <div class="form-group my-2">
        <label for="level-{{ $level->id }}-description">توضیحات سطح</label>
        <textarea class="form-control rounded" wire:model="description" id="level-{{ $level->id }}-description" cols="30" rows="10"></textarea>
        @error('description')
            <span class="form-text text-danger">{{ $message }}</span>
        @enderror
    </div>

    <hr>

    <x-buttons.btn-primary class="w-25" wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-primary>

</div>
