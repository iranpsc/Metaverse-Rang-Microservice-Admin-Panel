<div>
    <x-button class="my-2" data-bs-toggle="modal" data-bs-target="#create-package-modal">ایجاد پکیج رنگ</x-button>

    <x-form.search-box wire:model="search" />

    <x-modal id="create-package-modal" title="تعریف بسته">
        <div class="form-group row">
            <label for="asset" class="col-sm-4 col-form-label">ارز</label>
            <div class="col-sm-8">
                <select class="form-control" id="asset" wire:model="asset">
                    <option selected>ارز را انتخاب کنید</option>
                    @forelse ($variables as $variable)
                        <option value="{{ $variable->asset }}">{{ $variable->getAssetTitle() }}</option>
                    @empty
                        <option disabled>ارزی تعریف نشده است</option>
                    @endforelse
                </select>
                @error('asset')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <x-form.input label="تعداد" name="amount" />

        <x-form.input label="کد بسته" name="code" />

        <x-form.input type="file" label="تصویر" name="image" />

        @production
            <x-form.verification/>
        @endproduction

        <x-slot:footer>
            <x-button wire:loading.attr="disabled" wire:click="save">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot:footer>
    </x-modals.modal>

    @if ($options->count() > 0)
        <x-table>
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
                <tr wire:key="{{ $option->id }}">
                    <td>{{ $option->id }}</td>
                    <td>{{ $option->code }}</td>
                    <td>{{ $option->getAssetTitle() }}</td>
                    <td>{{ \App\Models\Variable::getRate($option->asset) * $option->amount }}</td>
                    <td>{{ $option->amount }}</td>
                    <td>{{ jdate($option->update_at)->format('Y-m-d') }}</td>
                    <td>
                        @if ($option->image)
                            <a href="{{ $option->image->url }}" target="_blank" class="btn btn-primary btn-sm round">مشاهده</a>
                        @endif
                    </td>
                    <td>{{ $option->note }}</td>
                    <td>
                        <x-button data-bs-toggle="modal" data-bs-target="#edit-package-modal-{{ $option->id }}">بروز رسانی</x-button>

                        <x-button color="danger" wire:confirm="آیا این پکیج را حذف می کنید؟" wire:click="delete({{ $option->id }})">حذف</x-button>

                        @if ($option->priceChangeLogs->count() > 0)
                            <x-button color="info" data-bs-toggle="modal"
                                data-bs-target="#option-history-{{ $option->id }}">تاریخچه تغییرات
                            </x-button>
                            <x-modal size="modal-xl" id="option-history-{{ $option->id }}" title="تاریخچه تغییرات">
                                    <x-table>
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
                                                    <tr wire:key="{{ $changeLog->id }}">
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $option->code }}</td>
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
                                    <x-button color="danger"  data-bs-dismiss="modal">بستن</x-button>
                                </x-slot:footer>
                            </x-modals.modal>
                        @endif
                        <livewire:variables.edit.edit-options :$option :key="$option->id"/>
                    </td>
                </tr>
            @endforeach
        </x-table>
        {{ $options->links() }}
    @else
        <x-alert type="warning" :message="'هیچ بسته ای تعریف نشده است'" />
    @endif
</div>
