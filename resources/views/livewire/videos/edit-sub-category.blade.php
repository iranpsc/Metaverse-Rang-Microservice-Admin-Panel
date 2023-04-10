<div>
    <x-modals.modal title="ویرایش دسته بندی" id="edit-sub-category-modal-{{ $subCategory->id }}">
        @if (session('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        <x-forms.group for="name" label="نام دسته بندی">
            <x-forms.input wire:model.lazy="name" />
            @error('name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="description" label="توضیحات">
            <textarea wire:model.lazy="description" class="form-control rounded" id="description" cols="30" rows="10"></textarea>
            @error('description')
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
                <x-forms.input wire:model="phoneVerification" placeholder="تایید پیامکی" />
                @error('phoneVerification')
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

        <x-slot name="footer">
            <x-buttons.btn-success wire:click="save" wire:loading.attr="disabled">ثبت</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>
</div>
