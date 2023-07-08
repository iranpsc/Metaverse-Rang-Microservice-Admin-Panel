<div>
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <x-forms.group for="name" label="نام">
                <x-forms.input wire:model.lazy="name" />
                @error('name')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="email" label="ایمیل">
                <x-forms.input wire:model.lazy="email" />
                @error('email')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="new_access_password" label="رمز دسترسی جدید ">
                <x-forms.input type="password" wire:model.lazy="new_access_password" />
                @error('new_access_password')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="new_access_password_confirmation" label="تایید رمز دسترسی جدید">
                <x-forms.input type="password" wire:model.lazy="new_access_password_confirmation" />
                @error('new_access_password_confirmation')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="password" label="رمز عبور جدید">
                <x-forms.input type="password" wire:model.lazy="password" />
                @error('password')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="password_confirmation" label="تایید رمز عبور جدید">
                <x-forms.input type="password" wire:model.lazy="password_confirmation" />
                @error('password_confirmation')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.group for="image" label="تصویر">
                <x-forms.input type="file" wire:model.lazy="image" />
                <span class="text-success" wire:loading wire:target="image">در حال بارگذاری ...</span>
                @error('image')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <x-forms.verification/>

            <x-buttons.btn-success class="btn-block" wire:click="save" wire:loading.attr="disabled">ثبت</x-buttons.btn-success>
        </div>
    </div>
</div>
