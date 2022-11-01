<div>
    <x-modals.modal id="create-prize" title="تعریف جوایز سلسله خانوادگی">
        @if (session()->has('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        <div class="row">
            <div class="col-sm-6">
                <x-forms.group for="member" label="نسبت خانوادگی">
                    <x-forms.select id="member" wire:model="prize.member">
                        <option selected value="">انتخاب کنید</option>
                        <option value="father">پدر</option>
                        <option value="mother">مادر</option>
                        <option value="life_partner">همسر</option>
                        <option value="sister">خواهر</option>
                        <option value="brother">برادر</option>
                        <option value="ofspring">فرزند</option>
                    </x-forms.select>
                    @error('prize.member')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
                <x-forms.group for="introduction-profit-increase" label="افزایش سود پاداش معرفی">
                    <x-forms.input id="introduction-profit-increase" wire:model="prize.introduction_profit_increase" />
                    @error('prize.introduction_profit_increase')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
                <x-forms.group for="accumulated-capital-reserve" label="ذخیره سرمایه انباشته">
                    <x-forms.input id="accumulated-capital-reserve" wire:model="prize.accumulated_capital_reserve" />
                    @error('prize.accumulated_capital_reserve')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
            </div>
            <div class="col-sm-6">
                <x-forms.group for="data-storage" label="ذخیره دیتا">
                    <x-forms.input name="data-storage" wire:model="prize.data_storage" />
                    @error('prize.data_storage')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
                <x-forms.group for="psc" label="پاداش معرفی PSC">
                    <x-forms.input name="psc" wire:model="prize.psc" />
                    @error('prize.psc')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
                <x-forms.group for="satisfaction" label="رضایت">
                    <x-forms.input name="satisfaction" wire:model="prize.satisfaction" />
                    @error('prize.satisfaction')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
            </div>
        </div>
        <x-slot name="footer">
            <x-buttons.btn-primary wire:click="save">ثبت</x-buttons.btn-primary>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>
</div>
