<div>
    <x-modal size="modal-xl" id="edit-prize-{{ $prize->id }}" title="ویرایش پاداشهای معرفی {{ $prize->getRelationTitle() }}">
        <div class="row">
            <div class="col-sm-6">
                <x-form.input id="introduction-profit-increase-{{ $prize->id }}" label="افزایش سود پاداش معرفی(%)" name="introduction_profit_increase" />
                <x-form.input id="accumulated-capital-reserve-{{ $prize->id }}" label="ذخیره سرمایه انباشته(%)" name="accumulated_capital_reserve" />
            </div>
            <div class="col-sm-6">
                <x-form.input id="data-storage-{{ $prize->id }}" label="ذخیره دیتا(%)" name="data_storage" />
                <x-form.input id="psc-{{ $prize->id }}" label="پاداش معرفی PSC (ریال)" name="psc" />
                <x-form.input id="satisfaction-{{ $prize->id }}" label="رضایت" name="satisfaction" />
            </div>
        </div>
        <x-slot name="footer">
            <x-button wire:loading.attr="disabled" wire:click="update">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot>
    </x-modals.modal>
</div>
