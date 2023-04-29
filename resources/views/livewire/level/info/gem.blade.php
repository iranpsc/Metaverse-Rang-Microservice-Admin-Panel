<div class="text-right">
    <div class="row">
        <div class="col-md-6">

            <x-forms.group for="level-{{ $level->id }}-name" label="نام سنگ">
                <x-forms.input id="level-{{ $level->id }}-name" wire:model="name" />
                @error('name')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-thread" label="تراشه نگین">
                <x-forms.input id="level-{{ $level->id }}-thread" wire:model="thread" />
                @error('thread')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-gem-pionts" label="تعداد پوینت های نگین">
                <x-forms.input id="level-{{ $level->id }}-gem-pionts" wire:model="points" />
                @error('points')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-volume" label="حجم نگین">
                <x-forms.input id="level-{{ $level->id }}-volume" wire:model="volume" />
                @error('volume')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>
            <x-forms.group for="level-{{ $level->id }}-color" label="رنگ نگین">
                <x-forms.input id="level-{{ $level->id }}-color" wire:model="color" />
                @error('color')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-gem-animation" label="انیمیشن">
                <x-forms.select wire:model="has_animation" id="level-{{ $level->id }}-gem-animation">
                    <option @selected($has_animation) value="0">ندارد</option>
                    <option @selected($has_animation) value="1">دارد</option>
                </x-forms.select>
                @error('animation')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

        </div>
        <div class="col-md-6">

            <x-forms.group for="level-{{ $level->id }}-png-file" label="فایل png نگین">
                <x-forms.input type="file" id="level-{{ $level->id }}-png-file" wire:model="png_file" />
                <x-progress-bar/>
                @error('png_file')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-fbx-file" label="فایل fbx نگین">
                <x-forms.input type="file" id="level-{{ $level->id }}-fbx-file" wire:model="fbx_file" />
                <x-progress-bar/>
                @error('fbx_file')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-gem-lines" label="تعداد خطوط مدل سه بعدی سنگ">
                <x-forms.input id="level-{{ $level->id }}-gem-lines" wire:model="lines" />
                @error('lines')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-encryption" label="رمزنگاری مرکزی">
                <x-forms.select wire:model="encryption" id="level-{{ $level->id }}-encryption">
                    <option @selected($encryption) value="0">خیر</option>
                    <option @selected($encryption) value="1">بله</option>
                </x-forms.select>
                @error('encryption')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="level-{{ $level->id }}-gem-designer" label="طراح نگین">
                <x-forms.input id="level-{{ $level->id }}-gem-designer" wire:model="designer" />
                @error('designer')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

        </div>
    </div>

    <div class="form-group my-2">
        <label for="level-{{ $level->id }}-gem-description">توضیحات نگین</label>
        <textarea class="form-control rounded" wire:model="description" id="level-{{ $level->id }}-gem-description"
            cols="30" rows="10"></textarea>
        @error('description')
            <span class="form-text text-danger">{{ $message }}</span>
        @enderror
    </div>

    <hr>
    <x-forms.verification id="{{ $level->id }}"/>
    <hr>

    <x-buttons.btn-primary class="w-25" wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-primary>

</div>
