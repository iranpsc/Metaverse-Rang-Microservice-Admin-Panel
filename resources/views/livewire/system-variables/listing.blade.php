<div>
    <x-button class="my-2" data-bs-toggle="modal" data-bs-target="#create-variable-modal">ایجاد متغیر</x-button>

    <x-form.search-box wire:model="search" />

    <x-modal id="create-variable-modal" title="تعریف متغیر">
        <x-form.input name="name" id="name" label="نام متغییر" />

        <x-form.input name="slug" id="slug" label="اسلاگ" />

        <x-form.input name="value" id="value" label="مقدار" />

        <x-form.verification/>

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
                <tr wire:key="{{ $variable->id }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $variable->name }}</td>
                    <td>{{ $variable->slug }}</td>
                    <td>{{ $variable->value }}</td>
                    <td>{{ jdate($variable->updated_at)->format('Y/m/d') }}</td>
                    <td>
                        <x-button data-bs-toggle="modal"
                            data-bs-target="#edit-system-variable-{{ $variable->id }}">ویرایش</x-button>
                        <x-button color="danger" wire:confirm="آیا می خواهید حذف کنید؟" wire:click="delete({{ $variable->id }})">حذف
                        </x-button>
                        @if ($variable->changeLogs->count() > 0)
                            <x-button color="info" data-bs-toggle="modal"
                                data-bs-target="#variable-history-{{ $variable->id }}">تاریخچه تغییرات
                            </x-button>
                            <x-modal size="modal-xl" id="variable-history-{{ $variable->id }}"
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
                        <livewire:system-variables.update :$variable :key="$variable->id">
                    </td>
                </tr>
            @endforeach
        </x-table>
        {{ $variables->links() }}
    @else
        <x-alert type="warning" :message="'متغیری ثبت نشده است!'"/>
    @endif
</div>
