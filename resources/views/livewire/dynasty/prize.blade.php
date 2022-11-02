<div>
    <x-buttons.btn-primary class="my-2" data-bs-toggle="modal" data-bs-target="#create-prize">تعریف جوایز
    </x-buttons.btn-primary>

    <x-modals.modal size="modal-xl" id="create-prize" title="تعریف جوایز سلسله خانوادگی">
        @if (session()->has('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        <div class="row">
            <div class="col-sm-6">
                <x-forms.group for="member" label="نسبت خانوادگی">
                    <x-forms.select id="member" wire:model="member">
                        <option selected value="">انتخاب کنید</option>
                        <option value="father">پدر</option>
                        <option value="mother">مادر</option>
                        <option value="husband">شوهر</option>
                        <option value="wife">زن</option>
                        <option value="sister">خواهر</option>
                        <option value="brother">برادر</option>
                        <option value="offspring">فرزند</option>
                    </x-forms.select>
                    @error('member')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
                <x-forms.group for="introduction-profit-increase" label="افزایش سود پاداش معرفی(%)">
                    <x-forms.input id="introduction-profit-increase" wire:model="introduction_profit_increase" />
                    @error('introduction_profit_increase')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
                <x-forms.group for="accumulated-capital-reserve" label="ذخیره سرمایه انباشته(%)">
                    <x-forms.input id="accumulated-capital-reserve" wire:model="accumulated_capital_reserve" />
                    @error('accumulated_capital_reserve')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
            </div>
            <div class="col-sm-6">
                <x-forms.group for="data-storage" label="ذخیره دیتا(%)">
                    <x-forms.input name="data-storage" wire:model="data_storage" />
                    @error('data_storage')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
                <x-forms.group for="psc" label="پاداش معرفی (ریال به psc)">
                    <x-forms.input name="psc" wire:model="psc" />
                    @error('psc')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
                <x-forms.group for="satisfaction" label="رضایت">
                    <x-forms.input name="satisfaction" wire:model="satisfaction" />
                    @error('satisfaction')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
            </div>
        </div>
        <x-slot name="footer">
            <x-buttons.btn-primary wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-primary>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>

    @if ($prizes->count() > 0)
        <x-tables.table>
            <x-slot name="headers">
                <th>نسبت خانوادگی</th>
                <th>جزپیات</th>
                <th>مدیریت</th>
            </x-slot>
            @foreach ($prizes as $prize)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ \App\Helpers\getRelationTitle($prize->member) }}</td>
                    <td>
                        <x-buttons.btn-info data-bs-toggle="modal" data-bs-target="#view-prize-{{ $prize->id }}">
                            مشاهده</x-buttons.btn-info>

                        <x-modals.modal size="modal-xl" id="view-prize-{{ $prize->id }}" title="جزئیات پاداش">
                            <div class="row">
                                <div class="col-sm-6">
                                    <x-forms.group for="member-{{ $prize->id }}" label="نسبت خانوادگی">
                                        <x-forms.input id="member-{{ $prize->id }}" disabled
                                            value="{{ \App\Helpers\getRelationTitle($prize->member) }}" />
                                    </x-forms.group>
                                    <x-forms.group for="introduction_profit_increase-{{ $prize->id }}"
                                        label="افزایش سود پاداش معرفی(%)">
                                        <x-forms.input id="introduction_profit_increase-{{ $prize->id }}" disabled
                                            value="{{ $prize->introduction_profit_increase * 100 }}" />
                                    </x-forms.group>
                                    <x-forms.group for="accumulated_capital_reserve-{{ $prize->id }}"
                                        label="ذخیره سرمایه انباشته(%)">
                                        <x-forms.input id="accumulated_capital_reserve-{{ $prize->id }}" disabled
                                            value="{{ $prize->accumulated_capital_reserve * 100 }}" />
                                    </x-forms.group>
                                </div>
                                <div class="col-sm-6">
                                    <x-forms.group for="data_storage-{{ $prize->id }}" label="ذخیره دیتا(%)">
                                        <x-forms.input id="data_storage-{{ $prize->id }}" disabled
                                            value="{{ $prize->data_storage * 100 }}" />
                                    </x-forms.group>
                                    <x-forms.group for="psc-{{ $prize->id }}" label="پاداش معرفی PSC (ریال)">
                                        <x-forms.input id="psc-{{ $prize->id }}" disabled
                                            value="{{ $prize->psc }}" />
                                    </x-forms.group>
                                    <x-forms.group for="satisfaction-{{ $prize->id }}" label="رضایت">
                                        <x-forms.input id="satisfaction-{{ $prize->id }}" disabled
                                            value="{{ $prize->satisfaction }}" />
                                    </x-forms.group>
                                </div>
                            </div>
                            <x-slot name="footer">
                                <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
                            </x-slot>
                        </x-modals.modal>
                    </td>
                    <td>
                        <x-buttons.btn-primary data-bs-toggle="modal" data-bs-target="#edit-prize-{{ $prize->id }}">
                            ویرایش</x-buttons.btn-primary>
                        <x-buttons.btn-danger wire:click="delete({{ $prize->id }})">حذف</x-buttons.btn-danger>
                        <livewire:dynasty.edit-prize :prize="$prize" :wire:key="'edit-prize-'.$prize->id">
                    </td>
                </tr>
            @endforeach
        </x-tables.table>
        {{ $prizes->links() }}
    @else
        <x-alerts.danger>پاداشی تعریف نشده است</x-alerts.danger>
    @endif
</div>
