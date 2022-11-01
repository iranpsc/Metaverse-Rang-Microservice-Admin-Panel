<div>
    {{-- Knowing others is intelligence; knowing yourself is true wisdom. --}}
    <x-modals.modal id="polygon-modal-{{ $polygon->id }}" title="وارد کردن اطلاعات نقشه به دیتابیس">
        @if (session('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif

        @if (session('error'))
            <x-alerts.danger>{{ session('error') }}</x-alerts.danger>
        @endif

        <div class="form-group row">
            <div class="col-sm-4">
                <x-buttons.btn-success wire:loading.attr="disabled" wire:click="sendCode">ارسال کد تایید</x-buttons.btn-success>
            </div>
            <div class="col-sm-8">
                <x-forms.input wire:model="code"/>
                @error('code')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <x-forms.group for="access-password-{{ $polygon->id }}" label="رمز دسترسی">
            <x-forms.input id="access-password-{{ $polygon->id }}" wire:model="accessPassword" />
            @error('accessPassword')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-slot name="footer">
            <x-buttons.btn-primary wire:loading.attr="disabled" wire:click="insertIntoDatabase({{ $polygon->id }})">ثبت نهایی</x-buttons.btn-primary>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>
</div>
