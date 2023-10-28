<div>
    <x-slot name="pageTitle">
        متغیرهای سیستم
    </x-slot>
    <x-button class="my-2" data-bs-toggle="modal" data-bs-target="#create-variable-modal">ایجاد متغیر</x-button>

    <x-forms.search-box wire:model="search" />

    <x-modals.modal id="create-variable-modal" title="تعریف متغیر">
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

        <x-forms.verification/>

        <x-slot name="footer">
            <x-button wire:loading.attr="disabled" wire:click="save">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot>
    </x-modals.modal>

    @if ($variables->count() > 0)
        <x-table>
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
                    <td>{{ jdate($variable->updated_at)->format('Y/m/d') }}</td>
                    <td>
                        <x-button data-bs-toggle="modal"
                            data-bs-target="#edit-system-variable-{{ $variable->id }}">ویرایش</x-button>
                        <x-button color="danger" class="confirm" id="{{ $variable->id }}" title="deleteSystemVariable">حذف
                        </x-button>
                        @if ($variable->changeLogs->count() > 0)
                            <x-button color="info" data-bs-toggle="modal"
                                data-bs-target="#variable-history-{{ $variable->id }}">تاریخچه تغییرات
                            </x-button>
                            <x-modals.modal size="modal-xl" id="variable-history-{{ $variable->id }}"
                                title="تاریخچه تغییرات">
                                <x-table>
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
                                                    <td>{{ jdate($changeLog->created_at)->format('Y/m/d') }}
                                                    </td>
                                                    <td>{{ jdate($changeLog->created_at)->format('H:m:s') }}
                                                    </td>
                                                    <td>{{ $changeLog->changer_name }}</td>
                                                    <td>{{ $changeLog->previous_value }}</td>
                                                    <td>{{ $changeLog->current_value }}</td>
                                                    <td>{{ $changeLog->note }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                </x-table>
                                <x-slot:footer>
                                    <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
                                </x-slot:footer>
                            </x-modals.modal>
                        @endif
                        <livewire:system-variables.update :variable="$variable" :wire:key="'eidt-system-variable'.$variable->id">
                    </td>
                </tr>
            @endforeach
        </x-table>
        {{ $variables->links() }}
    @else
        <x-alert type="warning" :message="'متغیری ثبت نشده است!'"/>
    @endif
</div>
