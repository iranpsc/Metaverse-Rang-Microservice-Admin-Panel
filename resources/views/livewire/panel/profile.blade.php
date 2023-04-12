<div>
    @if (session('success'))
        <x-alerts.success>{{ session('success') }}</x-alerts.success>
    @endif
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

    <x-forms.group for="image" label="تصویر">
        <x-forms.input type="file" wire:model.lazy="image" />
        <span class="text-success" wire:loading wire:target="image">در حال بارگذاری ...</span>
        @error('image')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </x-forms.group>

    <div class="row form-group">
        <div class="col-sm-4">
            <x-buttons.btn-success wire:loading.attr="disabled" wire:click="sendSMS">
                ارسال پیامک تایید
            </x-buttons.btn-success>
        </div>
        <div class="col-sm-8">
            <x-forms.input wire:model="code" placeholder="تایید پیامکی" />
            @error('code')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </div>

    </div>

    <x-forms.group label="رمز دسترسی" for="access_password">
        <x-forms.input type="password" id="access_password" wire:model="access_password" placeholder="رمز دسترسی" />
        @error('access_password')
            <span class="form-text text-danger">{{ $message }}</span>
        @enderror
    </x-forms.group>

    <x-buttons.btn-success class="w-25" wire:click="save" wire:loading.attr="disabled">ثبت</x-buttons.btn-success>
</div>
