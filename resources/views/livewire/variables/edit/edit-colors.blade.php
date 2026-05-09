<div>
    <x-modal id="edit-currency-modal-{{ $asset->id }}" title="ویرایش ارز">
        <x-form.input name="price" label="قیمت واحد" placeholder="قیمت واحد را به تومان وارد کنید" class="only-number" />

        <x-form.input type="file" name="image" label="تصویر ارز" placeholder="تصویر ارز را انتخاب کنید" />

        <x-form.input name="note" label="علت بروزرسانی" />

        @production
            <x-form.verification/>
        @endproduction

        <x-slot:footer>
            <x-button wire:loading.attr="disabled" wire:click="update">بروزرسانی</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot:footer>
    </x-modals.modal>
</div>
