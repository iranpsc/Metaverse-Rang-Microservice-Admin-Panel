@push('css')
    <link href="{{ asset('assets/plugins/kamadatepicker/kamadatepicker.min.css') }}" rel="stylesheet">
@endpush

<div>
    <x-modal id="edit-event-modal-{{ $event->id }}" title="ویرایش وقعه" size="modal-xl">

        <x-form.input name="title" label="عنوان" />

        <div class="form-group">
            <label for="content-{{ $event->id }}">متن</label>
            <div wire:ignore>
                <textarea id="content-{{ $event->id }}"></textarea>
            </div>
            @error('content')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <x-form.input type="color" name="color" label="رنگ" />

        <x-form.input type="file" name="image" label="تصویر" />

        <div class="form-group row" wire:ignore>
            <label for="start_date-{{ $event->id }}" class="form-col-label col-sm-4">تاریخ شروع</label>
            <div class="col-sm-8">
                <input type="text" name="start_date" id="start_date-{{ $event->id }}" class="form-control rounded"
                    placeholder="روز / ماه / سال">
                @error('start_date')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-group row" wire:ignore>
            <label for="end_date-{{ $event->id }}" class="form-col-label col-sm-4">تاریخ پایان</label>
            <div class="col-sm-8">
                <input type="text" name="end_date" id="end_date-{{ $event->id }}" class="form-control rounded"
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
            <x-button id="save-btn-{{ $event->id }}">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بازگشت</x-button>
        </x-slot>
    </x-modals.modal>

</div>

@push('scripts')
    <script src="{{ asset('assets/plugins/kamadatepicker/kamadatepicker.min.js') }}"></script>
@endpush

@script
    <script>
        let content_{{ $event->id }} = CKEDITOR.replace('content-{{ $event->id }}');
        let saveBtn = document.getElementById('save-btn-{{ $event->id }}');

        content_{{ $event->id }}.setData(`{!! $event->content !!}`);

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
        document.getElementById('edit-event-modal-{{ $event->id }}').addEventListener('shown.bs.modal', function() {
            kamaDatepicker('start_date-{{ $event->id }}', datePickerOptions);
            kamaDatepicker('end_date-{{ $event->id }}', datePickerOptions);

            // Set initial values from Livewire properties
            document.getElementById('start_date-{{ $event->id }}').value = '{{ $start_date }}';
            document.getElementById('end_date-{{ $event->id }}').value = '{{ $end_date }}';
        });

        saveBtn.addEventListener('click', function() {
            // Get date values from Persian date pickers
            const startDateValue = document.getElementById('start_date-{{ $event->id }}').value;
            const endDateValue = document.getElementById('end_date-{{ $event->id }}').value;

            // Set the values to Livewire properties
            $wire.set('content', content_{{ $event->id }}.getData());
            $wire.set('start_date', startDateValue);
            $wire.set('end_date', endDateValue);

            $wire.call('save');
        });
    </script>
@endscript
