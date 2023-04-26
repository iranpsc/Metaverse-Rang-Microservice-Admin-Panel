<div>
    <x-modals.modal id="edit-bank-account-modal-{{$account->id}}" title="ویرایش کردن اطلاعات بانکی کارمندان">
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

        <x-forms.verification id="{{ $account->id }}"/>

        <x-slot name="footer">
            <x-buttons.btn-success wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>
</div>
