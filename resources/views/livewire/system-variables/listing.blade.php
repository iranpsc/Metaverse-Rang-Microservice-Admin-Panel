<div>
    {{-- A good traveler has no fixed plans and is not intent upon arriving. --}}
    <x-buttons.btn-primary class="my-2" data-bs-toggle="modal" data-bs-target="#create-variable-modal">ایجاد متغیر
    </x-buttons.btn-primary>
    @if (session('success'))
        <x-alerts.success>{{ session('success') }}</x-alerts.success>
    @endif
    <x-modals.modal id="create-variable-modal" title="تعریف متغیر">
        @if (session('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        @if (session('error'))
            <x-alerts.danger>{{ session('error') }}</x-alerts.danger>
        @endif
        <x-forms.group for="name" label="نام متغییر">
            <x-forms.input wire:model="name" id="name" />
            @error('name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="slug" label="اسلاگ">
            <x-forms.input type="slug" wire:model="slug" id="slug" />
            @error('slug')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="value" label="مقدار">
            <x-forms.input type="value" wire:model="value" id="value" />
            @error('value')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <div class="form-group row">
            <div class="col-sm-4">
                <x-buttons.btn-success wire:loading.attr="disabled" wire:click="sendCode">ارسال کد تایید
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
            <x-forms.input type="password" id="access-password" wire:model="accessPassword" />
            @error('accessPassword')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-slot name="footer">
            <x-buttons.btn-success wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>

    @if ($variables->count() > 0)
        <x-tables.table>
            <x-slot name="headers">
                <th>نام</th>
                <th>اسلاگ</th>
                <th>مقدار</th>
                <th>آخرین بروزرسانی</th>
                <th>مدیریت</th>
            </x-slot>
            @foreach ($variables as $variable)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $variable->name }}</td>
                    <td>{{ $variable->slug }}</td>
                    <td>{{ $variable->value }}</td>
                    <td>{{ \Morilog\Jalali\Jalalian::forge($variable->updated_at)->format('Y/m/d') }}</td>
                    <td>
                        <x-buttons.btn-primary data-bs-toggle="modal"
                            data-bs-target="#edit-system-variable-{{ $variable->id }}">ویرایش</x-buttons.btn-primary>
                        <x-buttons.btn-danger class="confirm" id="{{ $variable->id }}" title="deleteSystemVariable">حذف
                        </x-buttons.btn-danger>
                        @if ($variable->changeLogs->count() > 0)
                            <x-buttons.btn-info data-bs-toggle="modal"
                                data-bs-target="#variable-history-{{ $variable->id }}">تاریخچه تغییرات
                            </x-buttons.btn-info>
                            <x-modals.modal size="modal-xl" id="variable-history-{{ $variable->id }}"
                                title="تاریخچه تغییرات">
                                <x-tables.table>
                                    <x-slot name="headers">
                                        <th>نام متغییر</th>
                                        <th>تاریخ تغییر</th>
                                        <th>ساعت تغییر</th>
                                        <th>تغییر دهنده</th>
                                        <th>وضعیت گذشته</th>
                                        <th>وضعیت حال</th>
                                        <th>توضیحات</th>
                                    </x-slot>
                                        <tbody>
                                            @foreach ($variable->changeLogs as $changeLog)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $changeLog->changeable->name }}</td>
                                                    <td>{{ \Morilog\Jalali\Jalalian::forge($changeLog->created_at)->format('Y/m/d') }}
                                                    </td>
                                                    <td>{{ \Morilog\Jalali\Jalalian::forge($changeLog->created_at)->format('H:m:s') }}
                                                    </td>
                                                    <td>{{ $changeLog->changer_name }}</td>
                                                    <td>{{ $changeLog->previous_value }}</td>
                                                    <td>{{ $changeLog->current_value }}</td>
                                                    <td>{{ $changeLog->note }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                </x-tables.table>
                                <x-slot:footer>
                                    <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
                                </x-slot:footer>
                            </x-modals.modal>
                        @endif
                <livewire:system-variables.update :variable="$variable" :wire:key="'eidt-system-variable'.$variable->id">
        </td>
        </tr>
        @endforeach
        </x-tables.table>
    @else
        <x-alerts.danger>متغیری تعریف نشده است!</x-alerts.danger>
        @endif
</div>
