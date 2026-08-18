<div>
    <x-modal id="edit-bank-account-modal-{{$account->id}}" title="ویرایش کردن اطلاعات بانکی کارمندان">
        <x-form.input name="bank_name" label="نام بانک" />

        <x-form.input name="shaba_num" label="شماره شبا" />

        <x-form.input name="card_num" label="شماره کارت" />

        <x-form.verification/>

        <x-slot name="footer">
            <x-button wire:click="save">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot>
    </x-modals.modal>
</div>
