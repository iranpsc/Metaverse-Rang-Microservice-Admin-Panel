<div>
    <x-button class="my-2" data-bs-toggle="modal" data-bs-target="#create-asset-modal">ایجاد ارز</x-button>

    <x-form.search-box wire:model="search" />

    <x-modal id="create-asset-modal" title="تعریف ارز">

        <x-form.input name="asset" label="نام ارز" placeholder="نام ارز را به انگلیسی وارد کنید" />

        <x-form.input name="price" label="قیمت واحد" placeholder="قیمت واحد را به تومان وارد کنید"
            class="only-number" />

        <x-form.input type="file" name="image" label="تصویر ارز" placeholder="تصویر ارز را انتخاب کنید" />

        @production
            <x-form.verification />
        @endproduction

        <x-slot:footer>
            <x-button wire:loading.attr="disabled" wire:click="save">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot:footer>
        </x-modals.modal>

        @if ($variables->count() > 0)
            <x-table>
                <x-slot:headers>
                    <th>نام ارز</th>
                    <th>قیمت واحد</th>
                    <th>تصویر</th>
                    <th>آخرین بروز رسانی</th>
                    <th>دلیل بروز رسانی</th>
                    <th>مدیریت</th>
                </x-slot:headers>
                @foreach ($variables as $variable)
                    <tr wire:key="{{ $variable->id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $variable->getAssetTitle() }}</td>
                        <td>{{ $variable->price }}</td>
                        <td>
                            @if ($variable->image)
                                <a href="{{ $variable->image->url }}" target="_blank">
                                    مشاهده تصویر
                                </a>
                            @else
                                <span class="text-danger">بدون تصویر</span>
                            @endif
                        </td>
                        <td>{{ jdate($variable->updated_at) }}</td>
                        <td>{{ $variable->note }}</td>
                        <td>
                            <x-button data-bs-toggle="modal"
                                data-bs-target="#edit-currency-modal-{{ $variable->id }}">بروزرسانی</x-button>
                            <x-button color="danger" wire:click="delete({{ $variable->id }})"
                                wire:confirm="آیا این ارز را حذف می کنید؟">حذف</x-button>

                            @if ($variable->priceChangeLogs->count() > 0)
                                <x-button color="info" data-bs-toggle="modal"
                                    data-bs-target="#variable-history-{{ $variable->id }}">تاریخچه تغییرات</x-button>

                                <x-modal size="modal-xl" id="variable-history-{{ $variable->id }}"
                                    title="تاریخچه تغییرات">
                                    <x-table>
                                        <x-slot name="headers">
                        <th>دارایی</th>
                        <th>تاریخ تغییر</th>
                        <th>ساعت تغییر</th>
                        <th>تغییر دهنده</th>
                        <th>وضعیت گذشته</th>
                        <th>وضعیت حال</th>
                        <th>توضیحات</th>
                        </x-slot>

                        @foreach ($variable->priceChangeLogs as $changeLog)
                    <tr wire:key="{{ $changeLog->id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>ارز {{ $changeLog->changeable->getAssetTitle() }}</td>
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
            </x-table>
            <x-slot:footer>
                <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
            </x-slot:footer>
            </x-modals.modal>
        @endif
        <livewire:variables.edit.edit-colors :asset="$variable" :key="'edit-asset-price-' . $variable->id">
            </td>
            </tr>
            @endforeach
            </x-table>
        @else
            <x-alert type="warning" :message="'هیچ ارزی ثبت نشده است'" />
            @endif

</div>
