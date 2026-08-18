<div>
    <x-button color="primary" class="my-2" data-bs-toggle="modal" data-bs-target="#create-prize">
        تعریف جوایز
    </x-button>

    <x-modal size="modal-xl" id="create-prize" title="تعریف جوایز سلسله خانوادگی">
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group row">
                    <label for="member" class="col-form-label col-md-4">نسبت خانوادگی</label>
                    <div class="col-md-8">
                        <select class="form-control rounded" id="member" wire:model="member">
                            <option selected value="">انتخاب کنید</option>
                            <option value="father">پدر</option>
                            <option value="mother">مادر</option>
                            <option value="husband">شوهر</option>
                            <option value="wife">زن</option>
                            <option value="sister">خواهر</option>
                            <option value="brother">برادر</option>
                            <option value="offspring">فرزند</option>
                        </select>
                        @error('member')
                            <span class="form-text text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <x-form.input name="introduction-profit-increase" label="افزایش سود پاداش معرفی(%)" />

                <x-form.input name="accumulated-capital-reserve" label="ذخیره سرمایه انباشته(%)" />

            </div>
            <div class="col-sm-6">
                <x-form.input name="data-storage" label="ذخیره دیتا(%)" />
                <x-form.input name="psc" label="پاداش معرفی PSC (ریال)" />
                <x-form.input name="satisfaction" label="رضایت" />
            </div>
        </div>
        <x-slot name="footer">
            <x-button wire:click="save">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot>
    </x-modals.modal>

    @if ($prizes->count() > 0)
        <x-table>
            <x-slot name="headers">
                <th>نسبت خانوادگی</th>
                <th>جزپیات</th>
                <th>مدیریت</th>
            </x-slot>
            @foreach ($prizes as $prize)
                <tr wire:key="{{ $prize->id }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $prize->getRelationTitle() }}</td>
                    <td>
                        <x-button color="info" data-bs-toggle="modal"
                            data-bs-target="#view-prize-{{ $prize->id }}">
                            مشاهده
                        </x-button>

                        <x-modal size="modal-xl" id="view-prize-{{ $prize->id }}" title="جزئیات پاداش">
                            <div class="row">
                                <div class="col-sm-12">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>نسبت خانوادگی</th>
                                                <th>افزایش سود پاداش معرفی(%)</th>
                                                <th>ذخیره سرمایه انباشته(%)</th>
                                                <th>ذخیره دیتا(%)</th>
                                                <th>پاداش معرفی PSC (ریال)</th>
                                                <th>رضایت</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($prizes as $prize)
                                                <tr>
                                                    <td>{{ $prize->getRelationTitle() }}</td>
                                                    <td>{{ $prize->introduction_profit_increase }}</td>
                                                    <td>{{ $prize->accumulated_capital_reserve }}</td>
                                                    <td>{{ $prize->data_storage }}</td>
                                                    <td>{{ $prize->psc }}</td>
                                                    <td>{{ $prize->satisfaction }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <x-slot name="footer">
                                <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
                            </x-slot>
                        </x-modals.modal>
                    </td>
                    <td>
                        <x-button color="info" data-bs-toggle="modal"
                            data-bs-target="#edit-prize-{{ $prize->id }}">
                            ویرایش
                        </x-button>
                        <x-button color="danger" wire:confirm="آیا می خواهید حذف کنید؟"
                            wire:click="delete({{ $prize->id }})">حذف</x-button>
                        <livewire:dynasty.edit-prize :$prize :key="$prize->id">
                    </td>
                </tr>
            @endforeach
        </x-table>
        {{ $prizes->links() }}
    @else
        <x-alert type="warning" :message="'جزئیاتی برای پاداش تعریف نشده است'" />
    @endif
</div>
