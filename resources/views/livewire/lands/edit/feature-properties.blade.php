<div>
    <x-modal id="modal-{{ explode('-', $feature->properties->id)[1] }}" title="ویرایش اطلاعات ملک">
        <x-form.input name="properties_id" label="کد زمین" />

        <x-form.input name="density" label="تراکم" />

        <x-form.input name="karbari" label="نوع کاربری" />

        <x-form.input name="address" label="آدرس" />

        <x-form.input name="rgb" label="قیمت گذاری" />

        <x-form.verification/>

        <x-slot:footer>
            <x-button wire:click="save">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot:footer>
    </x-modals.modal>
</div>
