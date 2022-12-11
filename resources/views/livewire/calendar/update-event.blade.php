<div>
    {{-- Close your eyes. Count to one. That is how long forever feels. --}}
    <x-modals.modal id="edit-event-modal-{{$event->id}}" title="ویرایش وقعه">
        <x-forms.group for="title-{{$event->id}}" label="عنوان">
            <x-forms.input id="title-{{$event->id}}" wire:model="title" />
            @error('title')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="content-{{$event->id}}" label="متن">
            <x-forms.input type="textarea" id="content-{{$event->id}}" wire:model="content" />
            @error('content')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="color-{{$event->id}}" label="رنگ">
            <x-forms.input id="color-{{$event->id}}" wire:model="color" />
            @error('color')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>


        <x-forms.group for="image-{{$event->id}}" label="عکس">
            <x-forms.input type="file" id="image-{{$event->id}}" wire:model="image" />
            <span class="text-success" wire:loading wire:target="image">در حال بارگذاری
                ...</span>
            @error('image')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="start_date-{{$event->id}}" label="تاریخ شروع">
            <x-forms.input type="date" id="start_date-{{$event->id}}" wire:model="start_date" />
            @error('start_date')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="end_date-{{$event->id}}" label="تاریخ پایان">
            <x-forms.input type="date" id="end_date-{{$event->id}}" wire:model="end_date" />
            @error('end_date')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="start_time-{{$event->id}}" label="ساعت شروع">
            <x-forms.input class="clockpicker-now" type="time" id="start_time-{{$event->id}}"
                wire:model="start_time" />
            @error('start_time')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="end_time-{{$event->id}}" label="ساعت پایان">
            <x-forms.input class="clockpicker-now" type="time" id="end_time-{{$event->id}}"
                wire:model="end_time" />
            @error('end_time')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>


        <x-slot name="footer">
            <x-buttons.btn-primary wire:loading.attr="disabled" wire:click="update">ثبت
            </x-buttons.btn-primary>
            <x-buttons.btn-danger data-bs-dismiss="modal">بازگشت</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>
</div>
