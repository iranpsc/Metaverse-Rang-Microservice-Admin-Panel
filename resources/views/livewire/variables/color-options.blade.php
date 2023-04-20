<div>
    <x-buttons.btn-success class="my-2" data-bs-toggle="modal" data-bs-target="#create-package-modal">ایجاد پکیج رنگ
    </x-buttons.btn-success>

    <x-modals.modal id="create-package-modal" title="تعریف بسته">
        @if (session()->has('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif

        <x-forms.group for="asset" label="رنگ">
            <x-forms.select id="asset" wire:model="asset">
                <option selected>ارز را انتخاب کنید</option>
                @forelse ($variables as $variable)
                    <option value="{{ $variable->asset }}">{{ \App\Helpers\getAssetColor($variable->asset) }}</option>
                @empty
                    <option disabled>ارزی تعریف نشده است</option>
                @endforelse
            </x-forms.select>
            @error('asset')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group label="تعداد" for="amount">
            <x-forms.input id="amount" wire:model="amount" placeholder="تعداد را وارد کنید" />
            @error('amount')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group label="کد بسته" for="code">
            <x-forms.input id="code" wire:model="code" placeholder="کد بسته را وارد کنید" />
            @error('code')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group label="تصویر" for="image">
            <x-forms.input type="file" id="image" wire:model="image" />
            <span class="text-success" wire:loading wire:target="image">در حال بارگذاری ...</span>
            @error('image')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.verification/>

        <x-slot:footer>
            <x-buttons.btn-primary wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-primary>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot:footer>
    </x-modals.modal>

    @if ($options->count() > 0)
        <x-tables.table>
            <x-slot:headers>
                <th>کد بسته</th>
                <th>ارز</th>
                <th>قیمت بسته</th>
                <th>تعداد</th>
                <th>تاریخ و ساعت بروزرسانی</th>
                <th>تصویر</th>
                <th>علت تغییر</th>
                <th>ملاحضات</th>
            </x-slot:headers>
            @forelse ($options as $option)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $option->code }}</td>
                    <td>{{ \App\Helpers\getAssetColor($option->asset) }}</td>
                    <td>{{ \App\Models\Variable::getRate($option->asset) * $option->amount }}</td>
                    <td>{{ $option->amount }}</td>
                    <td>{{ \Morilog\Jalali\Jalalian::forge($option->update_at) }}</td>
                    <th>
                        @if ($option->image)
                            <a href="{{ $option->image->url }}" target="_blank" class="btn btn-primary btn-sm round">مشاهده</a>
                        @endif
                    </th>
                    <td>{{ $option->note }}</td>
                    <td>
                        <x-buttons.btn-primary data-bs-toggle="modal"
                            data-bs-target="#edit-package-modal-{{ $option->id }}">بروز رسانی</x-buttons.btn-primary>
                        <x-buttons.btn-danger title="deletePackage" class="confirm" id="{{ $option->id }}">حذف
                        </x-buttons.btn-danger>
                        @if ($option->priceChangeLogs->count() > 0)
                        <x-buttons.btn-info data-bs-toggle="modal"
                            data-bs-target="#option-history-{{ $option->id }}">تاریخچه تغییرات
                        </x-buttons.btn-info>
                        <x-modals.modal size="modal-xl" id="option-history-{{ $option->id }}"
                            title="تاریخچه تغییرات">
                            <x-tables.table>
                                <x-slot name="headers">
                                    <th>کد بسته</th>
                                    <th>تاریخ تغییر</th>
                                    <th>ساعت تغییر</th>
                                    <th>تغییر دهنده</th>
                                    <th>وضعیت گذشته</th>
                                    <th>وضعیت حال</th>
                                    <th>توضیحات</th>
                                </x-slot>
                                    <tbody>
                                        @foreach ($option->priceChangeLogs as $changeLog)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $option->code }}</td>
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
                        <livewire:variables.edit.edit-options :option="$option" :wire:key="'edit-option-'.$option->id">
                    </td>
                </tr>
            @endforeach
        </x-tables.table>
        {{ $options->links() }}
    @else
        <x-alerts.danger>پکیجی تعریف نشده است</x-alerts.danger>
    @endif
</div>
