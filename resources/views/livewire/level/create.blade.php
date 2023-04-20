<div>
    <x-modals.modal id="create-level" title="تعریف سطح">
        @if (session()->has('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        <x-forms.group for="name" label="نام سطح">
            <x-forms.input id="name" wire:model="name" />
            @error('name')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group label="تصویر" for="image">
            <x-forms.input type="file" id="image" wire:model="image" />
            <span class="text-success" wire:loading wire:target="image">در حال بارگذاری ...</span>
            @error('image')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group label="تصویر پس زمینه" for="backgroundImage">
            <x-forms.input type="file" id="backgroundImage" wire:model="backgroundImage" />
            <span class="text-success" wire:loading wire:target="backgroundImage">در حال بارگذاری ...</span>
            @error('backgroundImage')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="slug" label="اسلاگ">
            <x-forms.input id="slug" wire:model="slug" />
            @error('slug')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="score" label="امتیاز مورد نیاز">
            <x-forms.input id="score" wire:model="score" />
            @error('score')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <div class="row form-group">
            <div class="col-sm-4">
                <x-buttons.btn-success wire:loading.attr="disabled" wire:click="sendSMS">
                    ارسال پیامک تایید
                </x-buttons.btn-success>
            </div>
            <div class="col-sm-8">
                <x-forms.input wire:model="phone_verification" placeholder="تایید پیامکی" />
                @error('phone_verification')
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

        <x-slot:footer>
            <x-buttons.btn-success wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بازگشت</x-buttons.btn-danger>
        </x-slot:footer>
    </x-modals.modal>
</div>
