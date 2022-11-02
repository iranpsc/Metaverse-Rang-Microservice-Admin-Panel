<div>
    <x-modals.modal size="modal-xl" id="edit-prize-{{ $prize->id }}" title="ویرایش پاداشهای معرفی {{ \App\Helpers\getRelationTitle($prize->member) }}">
        @if (session()->has('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        <div class="row">
            <div class="col-sm-6">
                <x-forms.group for="introduction-profit-increase-{{ $prize->id }}" label="افزایش سود پاداش معرفی(%)">
                    <x-forms.input id="introduction-profit-increase-{{ $prize->id }}" wire:model="introduction_profit_increase" />
                    @error('introduction_profit_increase')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
                <x-forms.group for="accumulated-capital-reserve-{{ $prize->id }}" label="ذخیره سرمایه انباشته(%)">
                    <x-forms.input id="accumulated-capital-reserve-{{ $prize->id }}" wire:model="accumulated_capital_reserve" />
                    @error('accumulated_capital_reserve')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
            </div>
            <div class="col-sm-6">
                <x-forms.group for="data-storage-{{ $prize->id }}" label="ذخیره دیتا(%)">
                    <x-forms.input name="data-storage-{{ $prize->id }}" wire:model="data_storage" />
                    @error('data_storage')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
                <x-forms.group for="psc-{{ $prize->id }}" label="پاداش معرفی PSC (ریال به psc)">
                    <x-forms.input name="psc-{{ $prize->id }}" wire:model="psc" />
                    @error('psc')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
                <x-forms.group for="satisfaction-{{ $prize->id }}" label="رضایت">
                    <x-forms.input name="satisfaction-{{ $prize->id }}" wire:model="satisfaction" />
                    @error('satisfaction')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
            </div>
        </div>
        <x-slot name="footer">
            <x-buttons.btn-primary wire:loading.attr="disabled" wire:click="update">ثبت</x-buttons.btn-primary>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>
</div>
