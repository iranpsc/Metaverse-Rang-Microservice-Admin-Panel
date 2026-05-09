@push('css')
    <link href="{{ asset('assets/plugins/kamadatepicker/kamadatepicker.min.css') }}" rel="stylesheet">
@endpush

<div>
    <x-button class="mb-2" data-bs-toggle="modal" data-bs-target="#create-event-modal">ایجاد وقعه</x-button>

    <x-modal size="modal-xl" id="create-event-modal" title="ایجاد وقعه">
        <x-form.input name="title" label="عنوان" />

        <div class="form-group">
            <label for="content">متن</label>
            <div wire:ignore>
                <textarea id="content"></textarea>
            </div>
            @error('content')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <x-form.input type="color" name="color" label="رنگ" />

        <x-form.input type="file" name="image" label="تصویر" />

        <div class="form-group row" wire:ignore>
            <label for="start_date" class="form-col-label col-sm-4">تاریخ شروع</label>
            <div class="col-sm-8">
                <input type="text" name="start_date" id="start_date" class="form-control rounded"
                    placeholder="روز / ماه / سال">
                @error('start_date')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-group row" wire:ignore>
            <label for="end_date" class="form-col-label col-sm-4">تاریخ پایان</label>
            <div class="col-sm-8">
                <input type="text" name="end_date" id="end_date" class="form-control rounded"
                    placeholder="روز / ماه / سال">
                @error('end_date')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <x-form.input type="time" name="start_time" label="ساعت شروع" />

        <x-form.input type="time" name="end_time" label="ساعت پایان" />

        <x-form.input name="btn_name" label="نام دکمه ورود به واقعه" />

        <x-form.input name="btn_link" label="لینک دکمه ورود به واقعه" />

        @production
            <x-form.verification />
        @endproduction

        <x-slot name="footer">
            <x-button id="save-btn">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بازگشت</x-button>
        </x-slot>
    </x-modal>

    @if ($events->count() > 0)
        <x-table>
            <x-slot name="headers">
                <th>عنوان</th>
                <th>متن</th>
                <th>رنگ</th>
                <th>زمان شروع</th>
                <th>زمان پایان</th>
                <th>تاریخ ثبت</th>
                <th>تصویر</th>
                <th>تعداد بازدید</th>
                <th>لایک</th>
                <th>دیسلایک</th>
                <th>وضعیت</th>
                <th>اقدامات</th>
            </x-slot>

            @foreach ($events as $event)
                <tr wire:key="{{ $event->id }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $event->title }}</td>
                    <td>{{ Str::limit($event->content, 20) }}</td>
                    <td>{{ $event->color }}</td>
                    <td>{{ jdate($event->starts_at)->format('Y/m/d H:i:s') }}</td>
                    <td>{{ $event->ends_at ? jdate($event->ends_at)->format('Y/m/d H:i:s') : '' }}</td>
                    <td>{{ jdate($event->created_at)->format('Y/m/d') }}</td>
                    <td>
                        <a target="_blank" href="{{ $event->image }}">مشاهده</a>
                    </td>
                    <td>{{ $event->views->count() }}</td>
                    <td>{{ $event->interactions->where('liked', 1)->count() }}</td>
                    <td>{{ $event->interactions->where('liked', 0)->count() }}</td>
                    <td>{{ $event->getStatus() }}</td>
                    <td>
                        <x-button data-bs-toggle="modal"
                            data-bs-target="#edit-event-modal-{{ $event->id }}">ویرایش</x-button>

                        <x-button color="danger" wire:confirm="آیا از حذف این وقعه اطمینان دارید؟"
                            wire:click="delete({{ $event->id }})">حذف</x-button>
                        <livewire:calendar.update :event="$event" :key="'event-' . $event->id" />
                    </td>
                </tr>
            @endforeach
        </x-table>

        {{ $events->links() }}
    @else
        <x-alert type="info">هیچ وقعه‌ای یافت نشد.</x-alert>
    @endif

</div>

@push('scripts')
    <script src="{{ asset('assets/plugins/kamadatepicker/kamadatepicker.min.js') }}"></script>
@endpush

@script
    <script>
        let content = CKEDITOR.replace('content');
        let saveBtn = document.getElementById('save-btn');

        CKEDITOR.editorConfig = function(config) {
            config.language = 'fa';
            config.uiColor = '#F7B42C';
            config.height = 300;
            config.toolbarCanCollapse = true;
        };

        // Initialize Persian date pickers
        var datePickerOptions = {
            placeholder: "روز / ماه / سال",
            twodigit: true,
            closeAfterSelect: true,
            nextButtonIcon: "fa fa-arrow-right",
            previousButtonIcon: "fa fa-arrow-left",
            buttonsColor: "gray",
            markToday: true,
            markHolidays: true,
            highlightSelectedDay: true,
            sync: true
        };

        // Initialize date pickers when modal is shown
        document.getElementById('create-event-modal').addEventListener('shown.bs.modal', function() {
            kamaDatepicker('start_date', datePickerOptions);
            kamaDatepicker('end_date', datePickerOptions);
        });

        saveBtn.addEventListener('click', function() {
            // Get date values from Persian date pickers
            const startDateValue = document.getElementById('start_date').value;
            const endDateValue = document.getElementById('end_date').value;

            // Set the values to Livewire properties
            $wire.set('content', content.getData());
            $wire.set('start_date', startDateValue);
            $wire.set('end_date', endDateValue);

            $wire.call('save');
        });
    </script>
@endscript
