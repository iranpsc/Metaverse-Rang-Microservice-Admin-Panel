<div>
    {{-- Success is as dangerous as failure. --}}

    <x-buttons.btn-primary data-bs-toggle="modal" data-bs-target="#create-event-modal">ایجاد وقعه
    </x-buttons.btn-primary>

    <x-modals.modal size="modal-xl" id="create-event-modal" title="ایجاد وقعه">

        <x-forms.group for="title" label="عنوان">
            <x-forms.input id="title" wire:model="title" />
            @error('title')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        {{-- <x-forms.group for="content" label="متن" wire:ignore>
            <textarea id="editor" wire:model="content" rows="25" cols="35">
            </textarea>
            @error('content')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group> --}}

        <x-forms.group for="content" label="متن">
            <textarea id="editor" wire:model="content" rows="10" cols="20" class="form-control">
            </textarea>
            @error('content')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>


        <x-forms.group for="color" label="رنگ">
            <x-forms.input id="color" wire:model="color" />
            @error('color')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>


        <x-forms.group for="image" label="عکس">
            <x-forms.input type="file" id="image" wire:model="image" />
            <span class="text-success" wire:loading wire:target="image">در حال بارگذاری ...</span>
            @error('image')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="start_date" label="تاریخ شروع">
            <x-forms.input type="date" id="start_date" wire:model="start_date" />
            @error('start_date')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="end_date" label="تاریخ پایان">
            <x-forms.input type="date" id="end_date" wire:model="end_date" />
            @error('end_date')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="start_time" label="ساعت شروع">
            <x-forms.input type="time" id="start_time" wire:model="start_time" />
            @error('start_time')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="end_time" label="ساعت پایان">
            <x-forms.input type="time" id="end_time" wire:model="end_time" />
            @error('end_time')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>


        <x-slot name="footer">
            <x-buttons.btn-primary wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-primary>
            <x-buttons.btn-danger data-bs-dismiss="modal">بازگشت</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>

    <ul class="nav nav-tabs border">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#tab1">وقایع شروع نشده</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab2">در حال اجرا</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab3">وقایع تمام شده</a>
        </li>
    </ul>

    <div class="tab-content">
        <div id="tab1" class="tab-pane fade active show">
            <livewire:calendar.comming-events :events="$events">
        </div>
        <div id="tab2" class="tab-pane fade">
            <livewire:calendar.now :events="$events">
        </div>
        <div id="tab3" class="tab-pane fade">
            <livewire:calendar.ago-events :events="$events">
        </div>
    </div>
