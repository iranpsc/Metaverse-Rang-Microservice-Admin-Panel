<div>
    <x-modals.modal id="edit-bank-account-modal-{{$account->id}}" title="ویرایش کردن اطلاعات بانکی کارمندان">

        @if (session('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif

        <x-forms.group for="bank_name_{{$account->id}}" label="نام بانک">
            <x-forms.input wire:model="bank_name" id="bank_name_{{$account->id}}" />
            @error('bank_name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="shaba_num_{{$account->id}}" label="شماره شبا">
            <x-forms.input type="shaba_num" wire:model="shaba_num" id="shaba_num_{{$account->id}}" />
            @error('shaba_num')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="card_num_{{$account->id}}" label="شماره کارت">
            <x-forms.input type="card_num" wire:model="card_num" id="card_num_{{$account->id}}" />
            @error('card_num')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <div class="form-group row">
            <div class="col-sm-4">
                <x-buttons.btn-success wire:loading.attr="disabled" wire:click="sendSMS">ارسال کد تایید
                </x-buttons.btn-success>
            </div>
            <div class="col-sm-8">
                <x-forms.input wire:model="code" />
                @error('code')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <x-forms.group for="access-password" label="رمز دسترسی">
            <x-forms.input type="password" id="access-password" wire:model="access_password" />
            @error('access_password')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-slot name="footer">
            <x-buttons.btn-success wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>
</div>
